<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AkdRegister extends Model
{
    use HasFactory;

    // Mengizinkan semua kolom diisi data dari form, kecuali kolom ID
    protected $guarded = ['id'];
}