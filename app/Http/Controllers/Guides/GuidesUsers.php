<?php

namespace App\Http\Controllers\Guides;

use App\Http\Controllers\Controller;
use App\Models\Regions;
use App\Models\RoleList;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class GuidesUsers extends Controller
{
    public function index()
    {
        $users = User::with(['regions', 'role'])->get();
        $regions = Regions::where('active', true)->get();
        $roles = RoleList::all();

        return view('Guides.GuidesUsersPage', compact('users', 'regions', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'role_id' => 'required|exists:role_lists,id',
            'regions' => 'required|array|min:1',
            'regions.*' => 'exists:regions,id',
            'password' => 'required|string|min:8|confirmed',
            'has_whatsapp' => 'boolean',
            'has_telegram' => 'boolean',
            'active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'role_id' => $validated['role_id'],
                'has_whatsapp' => $request->has('has_whatsapp'),
                'has_telegram' => $request->has('has_telegram'),
                'active' => $request->has('active'),
                'password' => Hash::make($validated['password']),
            ]);

            // Создаем записи в таблице users_to_regions
            foreach ($validated['regions'] as $regionId) {
                DB::table('users_to_regions')->insert([
                    'user_id' => $user->id,
                    'region_id' => $regionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('users.index')->with('success', 'Сотрудник успешно добавлен');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('users.index')->with('error', 'Произошла ошибка при добавлении сотрудника');
        }
    }

    public function edit(User $user)
    {
        return response()->json($user->load(['regions', 'role']));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
            'role_id' => 'required|exists:role_lists,id',
            'regions' => 'required|array|min:1',
            'regions.*' => 'exists:regions,id',
            'password' => 'nullable|string|min:8|confirmed',
            'has_whatsapp' => 'boolean',
            'has_telegram' => 'boolean',
            'active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'role_id' => $validated['role_id'],
                'has_whatsapp' => $request->has('has_whatsapp'),
                'has_telegram' => $request->has('has_telegram'),
                'active' => $request->has('active'),
            ];

            // Обновляем пароль только если он предоставлен
            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $user->update($userData);

            // Обновляем связи с регионами
            DB::table('users_to_regions')->where('user_id', $user->id)->delete();
            foreach ($validated['regions'] as $regionId) {
                DB::table('users_to_regions')->insert([
                    'user_id' => $user->id,
                    'region_id' => $regionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('users.index')->with('success', 'Сотрудник успешно обновлен');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('users.index')->with('error', 'Произошла ошибка при обновлении сотрудника');
        }
    }

    public function destroy(User $user)
    {
        try {
            DB::beginTransaction();

            // Удаляем связи с регионами
            DB::table('users_to_regions')->where('user_id', $user->id)->delete();
            
            // Удаляем пользователя
            $user->delete();

            DB::commit();
            return redirect()->route('users.index')->with('success', 'Сотрудник успешно удален');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('users.index')->with('error', 'Произошла ошибка при удалении сотрудника');
        }
    }
}
