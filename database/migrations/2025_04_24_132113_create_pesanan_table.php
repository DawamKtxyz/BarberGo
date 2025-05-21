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
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();
            $table->string('id_barber');
            $table->string('id_pelanggan');
            $table->date('tgl_pesanan');
            $table->string('nominal');
            $table->string('id_transaksi');
            $table->text('alamat_lengkap')->nullable(); // Kolom baru untuk alamat lengkap
            $table->decimal('ongkos_kirim', 10, 2)->default(10000); // Kolom baru untuk ongkos kirim, default 10000
            $table->string('email')->nullable(); // Kolom baru untuk email pelanggan
            $table->string('telepon', 15)->nullable(); // Kolom baru untuk telepon pelanggan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};
