<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogType extends Model
{
    use HasFactory;

    protected $guarded = false;

    public function logs()
    {
        return $this->hasMany(CompanyLog::class, 'type_id');
    }
}
