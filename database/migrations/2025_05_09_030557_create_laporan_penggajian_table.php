<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('laporan_penggajian', function (Blueprint $table) {
            $table->id('id_gaji'); // Primary key
            $table->unsignedBigInteger('id_pendapatan');
            $table->unsignedBigInteger('id_barber');
            $table->integer('jumlah_potong');
            $table->decimal('potongan_komisi', 10, 2);
            $table->decimal('total_gaji', 15, 2);
            $table->timestamps();

            // Foreign keys
            $table->foreign('id_pendapatan')->references('id_pendapatan')->on('pendapatan')->onDelete('cascade');
            $table->foreign('id_barber')->references('id')->on('tukang_cukur')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_penggajian');
    }
};
