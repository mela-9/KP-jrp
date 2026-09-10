<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratStockDetail extends Model
{
    protected $guarded = ['id'];

    public function block()
    {
        return $this->belongsTo(SuratBlock::class, 'surat_block_id');
    }
}