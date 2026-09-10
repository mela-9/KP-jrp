<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PolisQueue extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'allocated_surat_numbers' => 'array',
    ];
}