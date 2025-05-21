<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfilePhotoToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tukang_cukur', function (Blueprint $table) {
            $table->string('profile_photo')->nullable()->after('sertifikat');
        });

        Schema::table('pelanggan', function (Blueprint $table) {
            $table->string('profile_photo')->nullable()->after('tanggal_lahir');
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
            $table->dropColumn('profile_photo');
        });

        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropColumn('profile_photo');
        });
    }
}
