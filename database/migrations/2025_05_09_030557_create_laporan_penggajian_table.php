<?php
// Migration 1: create_laporan_penggajian_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLaporanPenggajianTable extends Migration
{
    public function up()
    {
        Schema::create('laporan_penggajian', function (Blueprint $table) {
            $table->id('id_gaji');
            $table->unsignedBigInteger('id_barber');
            $table->string('nama_barber');
            $table->integer('jumlah_pesanan')->default(0);
            $table->integer('jumlah_pelanggan')->default(0);
            $table->decimal('total_pendapatan', 15, 2)->default(0);
            $table->decimal('potongan_komisi', 15, 2)->default(0);
            $table->decimal('total_gaji', 15, 2)->default(0);
            $table->date('periode_dari');
            $table->date('periode_sampai');
            $table->enum('status', ['Belum Dibayar', 'Dibayar'])->default('Belum Dibayar');
            $table->timestamps();

            $table->foreign('id_barber')->references('id')->on('tukang_cukur')->onDelete('cascade');
            $table->index(['id_barber', 'periode_dari', 'periode_sampai']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('laporan_penggajian');
    }
}
