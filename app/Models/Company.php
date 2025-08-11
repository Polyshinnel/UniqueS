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
}
