<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AkdRegister extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Otomatisasi konversi array ke JSON
    protected $casts = [
        'nomor_surat_array' => 'array',
    ];
}