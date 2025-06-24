<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyContactsPhones extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_contact_id',
        'phone'
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(CompanyContacts::class, 'company_contact_id');
    }
}
