<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyEmails extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'email'
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
