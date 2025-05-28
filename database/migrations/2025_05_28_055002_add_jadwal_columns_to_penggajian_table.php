<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penggajian', function (Blueprint $table) {
            $table->unsignedBigInteger('jadwal_id')->nullable()->after('tanggal_pesanan');
            $table->date('tanggal_jadwal')->nullable()->after('jadwal_id');
            $table->time('jam_jadwal')->nullable()->after('tanggal_jadwal');

            $table->foreign('jadwal_id')->references('id')->on('jadwal_tukang_cukur')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('penggajian', function (Blueprint $table) {
            $table->dropForeign(['jadwal_id']);
            $table->dropColumn(['jadwal_id', 'tanggal_jadwal', 'jam_jadwal']);
        });
    }
};
