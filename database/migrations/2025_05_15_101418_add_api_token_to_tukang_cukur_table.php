<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApiTokenToTukangCukurTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tukang_cukur', function (Blueprint $table) {
            $table->string('api_token', 80)->after('password')
                ->nullable()
                ->default(null)
                ->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tukang_cukur', function (Blueprint $table) {
            $table->dropColumn('api_token');
        });
    }
}
