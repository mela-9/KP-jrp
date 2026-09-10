<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratBlock extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'allowed_polis' => 'array',
        'is_other_flag' => 'boolean',
    ];

    public function details()
    {
        return $this->hasMany(SuratStockDetail::class);
    }
}