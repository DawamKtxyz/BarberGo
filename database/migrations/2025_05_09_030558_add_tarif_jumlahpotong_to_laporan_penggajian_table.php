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
        Schema::table('laporan_penggajian', function (Blueprint $table) {
            // $table->integer('jumlah_potong')->default(0);
            $table->integer('tarif_per_potong')->default(0);});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_penggajian', function (Blueprint $table) {
            //
        });
    }
};
