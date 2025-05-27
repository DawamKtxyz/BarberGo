<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('chats', function (Blueprint $table) {
            // Make booking_id nullable untuk direct chat
            $table->unsignedBigInteger('booking_id')->nullable()->change();

            // Add index untuk direct chat lookup
            $table->index(['barber_id', 'pelanggan_id']);
        });
    }

    public function down()
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropIndex(['barber_id', 'pelanggan_id']);
            // Note: Tidak bisa revert nullable change dalam rollback
        });
    }
};
