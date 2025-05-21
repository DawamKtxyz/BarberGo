<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJadwalTukangCukurTable extends Migration
{
    public function up()
    {
        Schema::create('jadwal_tukang_cukur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tukang_cukur_id')
                  ->constrained('tukang_cukur')
                  ->onDelete('cascade');
            $table->date('tanggal');
            $table->time('jam');
            $table->timestamps(); // ini akan membuat created_at dan updated_at yang nullable secara default
        });
    }

    public function down()
    {
        Schema::dropIfExists('jadwal_tukang_cukur');
    }
}
