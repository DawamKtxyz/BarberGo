<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsVerifiedToTukangCukurTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tukang_cukur', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false)->after('sertifikat');
            $table->timestamp('verified_at')->nullable()->after('is_verified');
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
            $table->dropColumn(['is_verified', 'verified_at']);
        });
    }
}
