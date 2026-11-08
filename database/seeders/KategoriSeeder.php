<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kategori;


class KategoriSeeder extends Seeder
{
    public function run()
    {
        Kategori::create([
            'nama_kategori'=>'Modem'
        ]);

        Kategori::create([
            'nama_kategori'=>'Router'
        ]);

        Kategori::create([
            'nama_kategori'=>'Kabel'
        ]);

        Kategori::create([
            'nama_kategori'=>'Aksesoris'
        ]);
    }
}