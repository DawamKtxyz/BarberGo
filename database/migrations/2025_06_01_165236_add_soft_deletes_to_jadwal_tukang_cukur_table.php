<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeletesToJadwalTukangCukurTable extends Migration
{
    public function up()
    {
        Schema::table('jadwal_tukang_cukur', function (Blueprint $table) {
            $table->softDeletes(); // Ini akan menambah kolom deleted_at
        });
    }

    public function down()
    {
        Schema::table('jadwal_tukang_cukur', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
