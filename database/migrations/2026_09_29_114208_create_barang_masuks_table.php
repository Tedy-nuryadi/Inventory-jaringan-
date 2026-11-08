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
    Schema::create('barang_masuks', function (Blueprint $table) {

        $table->id();

        $table->foreignId('barang_id')
              ->constrained('barangs')
              ->cascadeOnDelete();

        $table->date('tanggal');

        $table->integer('jumlah');

        $table->string('supplier')->nullable();

        $table->text('keterangan')->nullable();

        $table->timestamps();

    });
}
};
