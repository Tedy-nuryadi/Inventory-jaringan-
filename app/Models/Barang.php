<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'kategori_id',
        'merk',
        'stok',
        'satuan',
        'lokasi',
        'stok_minimum'
    ];


    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }


    public function barangMasuk()
    {
        return $this->hasMany(BarangMasuk::class);
    }


    public function barangKeluar()
    {
        return $this->hasMany(BarangKeluar::class);
    }
    public function barangMasuks()
{
    return $this->hasMany(BarangMasuk::class);
}
}
