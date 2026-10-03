<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleRegister extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Baris ini wajib ada agar Laravel otomatis mengonversi Array ke JSON
    protected $casts = [
        'nomor_surat_array' => 'array',
    ];
}