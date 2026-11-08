<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('barang_keluars', function (Blueprint $table) {

            if (!Schema::hasColumn('barang_keluars', 'teknisi_id')) {

                $table->foreignId('teknisi_id')
                    ->nullable()
                    ->after('barang_id')
                    ->constrained('teknisis')
                    ->nullOnDelete();

            }

            if (!Schema::hasColumn('barang_keluars', 'foto')) {

                $table->string('foto')
                    ->nullable()
                    ->after('keperluan');

            }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barang_keluars', function (Blueprint $table) {

            if (Schema::hasColumn('barang_keluars', 'teknisi_id')) {

                $table->dropForeign(['teknisi_id']);

                $table->dropColumn('teknisi_id');

            }

            if (Schema::hasColumn('barang_keluars', 'foto')) {

                $table->dropColumn('foto');

            }

        });
    }
};