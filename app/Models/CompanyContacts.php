<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyContacts extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'position',
        'main_contact'
    ];

    protected $casts = [
        'main_contact' => 'boolean'
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function phones(): HasMany
    {
        return $this->hasMany(CompanyContactsPhones::class, 'company_contact_id');
    }

    public function emails(): HasMany
    {
        return $this->hasMany(CompanyContactsEmail::class, 'company_contact_id');
    }
}
