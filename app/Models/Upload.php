<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    protected $fillable = [
        'original_name',
        'path',
        'checksum',
        'completed'
    ];

    protected $casts = [
        'completed' => 'boolean'
    ];
}
