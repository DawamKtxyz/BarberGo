<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tukang_cukur', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('telepon', 15);
            $table->text('spesialisasi')->nullable();
            $table->decimal('harga', 10, 2)->default(20000); // Ganti persentase_komisi menjadi harga dengan presisi yang lebih tinggi
            $table->string('sertifikat');
            $table->decimal('persentase_komisi', 5, 2)->default(0.05);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tukang_cukur');
    }
};
