<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('barangs', function (Blueprint $table) {

        $table->id();

        $table->string('kode_barang');

        $table->string('nama_barang');

        $table->foreignId('kategori_id')
              ->constrained('kategoris')
              ->cascadeOnDelete();

        $table->string('merk')->nullable();

        $table->integer('stok')->default(0);

        $table->string('satuan');

        $table->string('lokasi')->nullable();

        $table->integer('stok_minimum')->default(5);

        $table->timestamps();

    });
}
};
