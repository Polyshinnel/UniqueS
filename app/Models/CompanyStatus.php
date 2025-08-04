<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyStatus extends Model
{
    protected $table = 'company_statuses';
    protected $guarded = false;
    
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'company_status_id');
    }
}
