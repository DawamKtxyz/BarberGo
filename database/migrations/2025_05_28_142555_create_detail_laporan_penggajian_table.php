<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetailLaporanPenggajianTable extends Migration
{
    public function up()
    {
        Schema::create('detail_laporan_penggajian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_laporan');
            $table->unsignedBigInteger('id_pesanan');
            $table->unsignedBigInteger('id_pelanggan');
            $table->string('nama_pelanggan');
            $table->date('tanggal_pesanan');
            $table->decimal('nominal_bayar', 15, 2);
            $table->timestamps();

            $table->foreign('id_laporan')->references('id_gaji')->on('laporan_penggajian')->onDelete('cascade');
            $table->foreign('id_pesanan')->references('id')->on('pesanan')->onDelete('cascade');
            $table->foreign('id_pelanggan')->references('id')->on('pelanggan')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('detail_laporan_penggajian');
    }
}
