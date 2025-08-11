<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyContactsEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_contact_id',
        'email',
        'is_primary'
    ];

    protected $casts = [
        'is_primary' => 'boolean'
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(CompanyContacts::class, 'company_contact_id');
    }
}
