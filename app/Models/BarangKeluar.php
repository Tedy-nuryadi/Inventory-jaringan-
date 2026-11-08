<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangKeluar extends Model
{
    protected $fillable = [
        'barang_id',
        'teknisi_id',
        'tanggal',
        'jumlah',
        'pengguna',
        'keperluan',
        'tujuan',
        'foto',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function teknisi()
    {
        return $this->belongsTo(Teknisi::class);
    }
}