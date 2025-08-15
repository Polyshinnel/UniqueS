<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvertisementsTags extends Model
{
    use HasFactory;

    protected $table = 'advertisements_tags';
    
    protected $fillable = [
        'advertisement_id',
        'tag'
    ];

    public function advertisement()
    {
        return $this->belongsTo(Advertisement::class);
    }
}
