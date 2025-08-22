<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvAction extends Model
{
    use HasFactory;

    protected $guarded = false;

    protected $casts = [
        'expired_at' => 'datetime',
        'completed_at' => 'datetime',
        'status' => 'boolean',
    ];

    public function advertisement()
    {
        return $this->belongsTo(Advertisement::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
