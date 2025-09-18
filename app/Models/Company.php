<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'inn',
        'source_id',
        'region_id',
        'regional_user_id',
        'owner_user_id',
        'email',
        'phone',
        'site',
        'common_info',
        'company_status_id',
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(CompanyContacts::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CompanyAddress::class);
    }

    public function regional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'regional_user_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(CompanyStatuses::class, 'company_status_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Regions::class, 'region_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Sources::class, 'source_id');
    }

    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouses::class, 'company_to_warehouses', 'company_id', 'warehouse_id');
    }

    public function emails(): HasMany
    {
        return $this->hasMany(CompanyEmails::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(CompanyActions::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Смена ответственного за компанию
     * 
     * @param int $newOwnerId ID нового ответственного
     * @param \Illuminate\Contracts\Auth\Authenticatable $currentUser Текущий пользователь (должен быть администратором)
     * @return bool
     * @throws \Exception
     */
    public function changeOwner(int $newOwnerId, $currentUser): bool
    {
        // Проверяем права доступа - только администраторы могут менять ответственного
        if (!$this->canChangeOwner($currentUser)) {
            throw new \Exception('Недостаточно прав для смены ответственного. Только администраторы могут выполнять эту операцию.');
        }

        // Получаем нового ответственного
        $newOwner = User::find($newOwnerId);
        if (!$newOwner) {
            throw new \Exception('Пользователь с указанным ID не найден.');
        }

        // Проверяем, что новый ответственный может быть назначен
        if (!$this->canBeOwner($newOwner)) {
            throw new \Exception('Указанный пользователь не может быть назначен ответственным. Доступны только администраторы и менеджеры, привязанные к складам компании.');
        }

        // Начинаем транзакцию
        \DB::beginTransaction();
        
        try {
            // Обновляем ответственного в компании
            $this->owner_user_id = $newOwnerId;
            $this->save();

            // Обновляем owner_id для всех товаров компании
            $this->products()->update(['owner_id' => $newOwnerId]);

            // Обновляем created_by для всех объявлений товаров компании
            $productIds = $this->products()->pluck('id');
            Advertisement::whereIn('product_id', $productIds)->update(['created_by' => $newOwnerId]);

            \DB::commit();
            return true;
            
        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    /**
     * Проверяет, может ли пользователь менять ответственного
     * 
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @return bool
     */
    public function canChangeOwner($user): bool
    {
        // Только администраторы могут менять ответственного
        return $user->role && $user->role->name === 'Администратор';
    }

    /**
     * Проверяет, может ли пользователь быть назначен ответственным
     * 
     * @param User $user
     * @return bool
     */
    public function canBeOwner(User $user): bool
    {
        // Проверяем роль - только администраторы и менеджеры
        if (!$user->role || !in_array($user->role->name, ['Администратор', 'Менеджер'])) {
            return false;
        }

        // Проверяем, что пользователь привязан к складам компании
        $companyWarehouseIds = $this->warehouses()->pluck('warehouses.id');
        $userWarehouseIds = $user->warehouses()->pluck('warehouses.id');
        
        // Пользователь должен быть привязан хотя бы к одному складу компании
        return $companyWarehouseIds->intersect($userWarehouseIds)->isNotEmpty();
    }

    /**
     * Получает список пользователей, которые могут быть назначены ответственными
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableOwners()
    {
        $companyWarehouseIds = $this->warehouses()->pluck('warehouses.id');
        
        return User::whereHas('role', function ($query) {
            $query->whereIn('name', ['Администратор', 'Менеджер']);
        })
        ->whereHas('warehouses', function ($query) use ($companyWarehouseIds) {
            $query->whereIn('warehouses.id', $companyWarehouseIds);
        })
        ->with(['role', 'warehouses'])
        ->get();
    }

    /**
     * Смена регионального представителя компании
     * 
     * @param int $newRegionalId ID нового регионального представителя
     * @param \Illuminate\Contracts\Auth\Authenticatable $currentUser Текущий пользователь (должен быть администратором)
     * @return bool
     * @throws \Exception
     */
    public function changeRegional(int $newRegionalId, $currentUser): bool
    {
        // Проверяем права доступа - только администраторы могут менять регионального представителя
        if (!$this->canChangeRegional($currentUser)) {
            throw new \Exception('Недостаточно прав для смены регионального представителя. Только администраторы могут выполнять эту операцию.');
        }

        // Получаем нового регионального представителя
        $newRegional = User::find($newRegionalId);
        if (!$newRegional) {
            throw new \Exception('Пользователь с указанным ID не найден.');
        }

        // Проверяем, что новый региональный представитель может быть назначен
        if (!$this->canBeRegional($newRegional)) {
            throw new \Exception('Указанный пользователь не может быть назначен региональным представителем. Доступны только региональные представители, привязанные к складам компании.');
        }

        // Начинаем транзакцию
        \DB::beginTransaction();
        
        try {
            // Обновляем регионального представителя в компании
            $this->regional_user_id = $newRegionalId;
            $this->save();

            // Обновляем regional_id для всех товаров компании
            $this->products()->update(['regional_id' => $newRegionalId]);

            \DB::commit();
            return true;
            
        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    /**
     * Проверяет, может ли пользователь менять регионального представителя
     * 
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @return bool
     */
    public function canChangeRegional($user): bool
    {
        // Только администраторы могут менять регионального представителя
        return $user->role && $user->role->name === 'Администратор';
    }

    /**
     * Проверяет, может ли пользователь быть назначен региональным представителем
     * 
     * @param User $user
     * @return bool
     */
    public function canBeRegional(User $user): bool
    {
        // Проверяем роль - только региональные представители
        if (!$user->role || $user->role->name !== 'Региональный представитель') {
            return false;
        }

        // Проверяем, что пользователь привязан к складам компании
        $companyWarehouseIds = $this->warehouses()->pluck('warehouses.id');
        $userWarehouseIds = $user->warehouses()->pluck('warehouses.id');
        
        // Пользователь должен быть привязан хотя бы к одному складу компании
        return $companyWarehouseIds->intersect($userWarehouseIds)->isNotEmpty();
    }

    /**
     * Получает список пользователей, которые могут быть назначены региональными представителями
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableRegionals()
    {
        $companyWarehouseIds = $this->warehouses()->pluck('warehouses.id');
        
        return User::whereHas('role', function ($query) {
            $query->where('name', 'Региональный представитель');
        })
        ->whereHas('warehouses', function ($query) use ($companyWarehouseIds) {
            $query->whereIn('warehouses.id', $companyWarehouseIds);
        })
        ->with(['role', 'warehouses'])
        ->get();
    }
}
