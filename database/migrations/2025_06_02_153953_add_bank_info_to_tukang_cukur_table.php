<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tukang_cukur', function (Blueprint $table) {
            $table->string('nama_bank')->nullable()->after('persentase_komisi');
        });
    }

    public function down()
    {
        Schema::table('tukang_cukur', function (Blueprint $table) {
            $table->dropColumn(['nama_bank']);
        });
    }
};
