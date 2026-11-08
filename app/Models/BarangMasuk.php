<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model
{

    protected $fillable = [

        'barang_id',
        'tanggal',
        'jumlah',
        'supplier',
        'keterangan'

    ];


    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

}