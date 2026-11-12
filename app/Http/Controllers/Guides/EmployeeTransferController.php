<?php

namespace App\Http\Controllers\Guides;

use App\Http\Controllers\Controller;
use App\Models\Regions;
use App\Models\User;
use App\Services\EmployeeTransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EmployeeTransferController extends Controller
{
    public function index()
    {
        $users = User::whereHas('role', fn ($query) => $query->whereIn('name', EmployeeTransferService::ROLES))
            ->with('role')->orderBy('name')->get()->map(fn ($user) => [
                'id' => $user->id, 'name' => $user->name, 'active' => $user->active,
                'role' => array_search($user->role->name, EmployeeTransferService::ROLES, true),
            ]);

        return view('Guides.EmployeeTransferPage', compact('users'));
    }

    public function options(Request $request, EmployeeTransferService $service)
    {
        $data = $this->parameters($request);
        $service->source($data);
        $regions = Regions::whereIn('id', $service->companies(array_merge($data, ['region_id' => null]))->select('region_id'))
            ->orderBy('name')->get(['id', 'name']);
        $companies = $service->companies($data);
        $targets = User::where('id', '!=', $data['source_id'])->where('active', true)
            ->whereHas('role', fn ($query) => $query->where('name', EmployeeTransferService::ROLES[$data['role']]))
            ->orderBy('name')->get();
        $targets = $targets->map(fn ($user) => [
            'id' => $user->id, 'name' => $user->name, 'count' => $service->eligible($companies, $user)->count(),
        ])->filter(fn ($user) => $user['count'] > 0)->values();

        return response()->json(['regions' => $regions, 'targets' => $targets, 'total' => $companies->count()]);
    }

    public function preview(Request $request, EmployeeTransferService $service)
    {
        $data = $this->parameters($request, true);
        $snapshot = $service->snapshot($data);
        $token = (string) Str::uuid();
        // One current confirmation per session; a new preview supersedes the old one.
        $request->session()->put('employee_transfer', [
            'token' => $token, 'data' => $data, 'fingerprint' => $snapshot['fingerprint'],
            'expires_at' => now()->addMinutes(15)->timestamp,
        ]);

        return response()->json([
            'token' => $token, 'total' => $snapshot['total'], 'eligible' => $snapshot['eligible'],
            'remaining' => $snapshot['remaining'], 'source' => $snapshot['source']->name,
            'target' => $snapshot['target']->name,
        ]);
    }

    public function store(Request $request, EmployeeTransferService $service)
    {
        $request->validate(['token' => 'required|uuid']);
        $preview = $request->session()->get('employee_transfer');
        if (!$preview || !hash_equals($preview['token'], $request->input('token')) || $preview['expires_at'] < now()->timestamp) {
            throw ValidationException::withMessages(['transfer' => 'Подтверждение устарело. Обновите подсчёт и повторите передачу.']);
        }

        $result = $service->transfer($preview['data'], $preview['fingerprint'], $request->user());
        $request->session()->forget('employee_transfer');

        return response()->json($result + ['message' => "Сотрудник изменён в {$result['transferred']} компаниях. Осталось у текущего сотрудника: {$result['remaining']}."]);
    }

    private function parameters(Request $request, bool $withTarget = false): array
    {
        $rules = [
            'role' => ['required', Rule::in(array_keys(EmployeeTransferService::ROLES))],
            'source_id' => 'required|integer|exists:users,id',
            'region_id' => 'nullable|integer|exists:regions,id',
        ];
        if ($withTarget) {
            $rules['target_id'] = 'required|integer|exists:users,id|different:source_id';
        }

        return $request->validate($rules);
    }
}
