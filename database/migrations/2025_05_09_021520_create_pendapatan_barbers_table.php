<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePendapatanBarbersTable extends Migration // Ubah nama class ini
{
    /**
     * Jalankan migrasi untuk membuat tabel.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pendapatan', function (Blueprint $table) {
            $table->id('id_pendapatan');
            $table->unsignedBigInteger('id_pesanan');
            $table->unsignedBigInteger('id_barber');
            $table->unsignedBigInteger('id_pelanggan');
            $table->date('tanggal_bayar');
            $table->decimal('nominal_bayar', 15, 2);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('id_pesanan')->references('id')->on('pesanan')->onDelete('cascade');
            $table->foreign('id_barber')->references('id')->on('tukang_cukur')->onDelete('cascade');
            $table->foreign('id_pelanggan')->references('id')->on('pelanggan')->onDelete('cascade');
        });
    }

    /**
     * Balikkan perubahan jika migrasi dirollback.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pendapatan');
    }
}
