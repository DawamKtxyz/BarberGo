<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('barber_id');
            $table->unsignedBigInteger('pelanggan_id');
            $table->text('last_message')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->integer('barber_unread_count')->default(0);
            $table->integer('pelanggan_unread_count')->default(0);
            $table->timestamps();

            $table->foreign('booking_id')->references('id')->on('pesanan')->onDelete('cascade');
            $table->foreign('barber_id')->references('id')->on('tukang_cukur')->onDelete('cascade');
            $table->foreign('pelanggan_id')->references('id')->on('pelanggan')->onDelete('cascade');

            $table->unique('booking_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chats');
    }
};
