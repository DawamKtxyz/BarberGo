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
        Schema::create('penggajian', function (Blueprint $table) {
            $table->id('id_gaji');
            $table->unsignedBigInteger('id_pesanan');
            $table->unsignedBigInteger('id_barber');
            $table->string('nama_barber');
            $table->string('rekening_barber');
            $table->unsignedBigInteger('id_pelanggan');
            $table->string('nama_pelanggan');
            $table->timestamp('tanggal_pesanan');
            $table->decimal('total_bayar', 10, 2);
            $table->decimal('potongan', 10, 2)->default(0);
            $table->decimal('total_gaji', 10, 2);
            $table->enum('status', ['lunas', 'belum lunas'])->default('belum lunas');
            $table->string('bukti_transfer')->nullable();
            $table->timestamps();

            $table->foreign('id_pesanan')->references('id')->on('pesanan')->onDelete('cascade');
            $table->foreign('id_barber')->references('id')->on('tukang_cukur')->onDelete('cascade');
            $table->foreign('id_pelanggan')->references('id')->on('pelanggan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penggajian');
    }
};
