<?php

namespace App\Services;

use App\Models\Advertisement;
use App\Models\Company;
use App\Models\CompanyLog;
use App\Models\LogType;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeTransferService
{
    public const ROLES = ['manager' => 'Менеджер', 'regional' => 'Региональный представитель'];

    public function source(array $data, bool $lock = false): User
    {
        $query = User::with('role')->whereKey($data['source_id']);
        $source = ($lock ? $query->lockForUpdate() : $query)->first();
        if (!$source || $source->role?->name !== self::ROLES[$data['role']]) {
            throw ValidationException::withMessages(['source_id' => 'Выберите сотрудника указанной роли.']);
        }

        return $source;
    }

    public function companies(array $data): Builder
    {
        return Company::query()->where($this->column($data['role']), $data['source_id'])
            ->when($data['region_id'] ?? null, fn ($query, $id) => $query->where('region_id', $id));
    }

    public function eligible(Builder $companies, User $target): Builder
    {
        return (clone $companies)->whereHas('warehouses', function ($query) use ($target) {
            $query->whereIn('warehouses.id', $target->warehouses()->select('warehouses.id'));
        });
    }

    public function snapshot(array $data, bool $lock = false): array
    {
        $source = $this->source($data, $lock);
        $targetQuery = User::with('role')->whereKey($data['target_id']);
        $target = ($lock ? $targetQuery->lockForUpdate() : $targetQuery)->first();
        if (!$target || !$target->active || $target->id === $source->id || $target->role?->name !== self::ROLES[$data['role']]) {
            throw ValidationException::withMessages(['target_id' => 'Выберите другого активного сотрудника той же роли.']);
        }

        $query = $this->companies($data)->orderBy('id');
        $companies = ($lock ? $query->lockForUpdate() : $query)->get(['id', 'updated_at']);
        $ids = $companies->modelKeys();
        // Lock membership rows as well as companies before checking warehouse access.
        $warehouseQuery = DB::table('users_to_warehouses')->where('user_id', $target->id)->orderBy('warehouse_id');
        $warehouses = ($lock ? $warehouseQuery->lockForUpdate() : $warehouseQuery)->pluck('warehouse_id')->all();
        $linksQuery = DB::table('company_to_warehouses')->whereIn('company_id', $ids)->orderBy('company_id')->orderBy('warehouse_id');
        $links = ($lock ? $linksQuery->lockForUpdate() : $linksQuery)->get(['company_id', 'warehouse_id']);
        $eligibleIds = $links->whereIn('warehouse_id', $warehouses)->pluck('company_id')->unique()->sort()->values()->all();

        return [
            'source' => $source,
            'target' => $target,
            'ids' => $eligibleIds,
            'total' => count($ids),
            'eligible' => count($eligibleIds),
            'remaining' => count($ids) - count($eligibleIds),
            'fingerprint' => hash('sha256', json_encode([$data, $companies->toArray(), $warehouses, $links->toArray()])),
        ];
    }

    public function transfer(array $data, string $fingerprint, User $actor): array
    {
        abort_unless($actor->role?->name === 'Администратор', 403);

        return DB::transaction(function () use ($data, $fingerprint, $actor) {
            $snapshot = $this->snapshot($data, true);
            if (!hash_equals($fingerprint, $snapshot['fingerprint'])) {
                throw ValidationException::withMessages(['transfer' => 'Данные изменились. Обновите подсчёт и подтвердите передачу заново.']);
            }
            if (!$snapshot['eligible']) {
                throw ValidationException::withMessages(['transfer' => 'Нет компаний, доступных для передачи.']);
            }

            $type = LogType::firstOrCreate(['name' => 'Комментарий'], ['color' => '#133E71']);
            $targetId = $snapshot['target']->id;
            $assignment = $data['role'] === 'manager' ? 'ответственного' : 'регионального представителя';
            $text = "Массовая замена: пользователь {$actor->name} сменил {$assignment} с '{$snapshot['source']->name}' (ID: {$snapshot['source']->id}) на '{$snapshot['target']->name}' (ID: {$targetId}).";
            $now = now();

            foreach (array_chunk($snapshot['ids'], 300) as $ids) {
                Company::whereIn('id', $ids)->update([$this->column($data['role']) => $targetId]);
                $products = Product::whereIn('company_id', $ids);
                $products->update([$data['role'] === 'manager' ? 'owner_id' : 'regional_id' => $targetId]);
                if ($data['role'] === 'manager') {
                    Advertisement::whereIn('product_id', $products->select('id'))->update(['created_by' => $targetId]);
                }
                CompanyLog::insert(array_map(fn ($id) => [
                    'company_id' => $id,
                    'user_id' => $actor->id,
                    'type_id' => $type->id,
                    'log' => $text,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $ids));
            }

            return ['transferred' => $snapshot['eligible'], 'remaining' => $snapshot['remaining']];
        });
    }

    private function column(string $role): string
    {
        return $role === 'manager' ? 'owner_user_id' : 'regional_user_id';
    }
}
