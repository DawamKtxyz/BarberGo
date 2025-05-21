<?php

// 1. Migration untuk menambah kolom status_pembayaran pada tabel pesanan
// File: database/migrations/2025_05_20_create_add_payment_status_to_pesanan_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->enum('status_pembayaran', ['pending', 'paid', 'failed', 'expired'])->default('pending')->after('telepon');
            $table->string('payment_url')->nullable()->after('status_pembayaran');
            $table->string('payment_token')->nullable()->after('payment_url');
            $table->string('payment_method')->nullable()->after('payment_token');
            $table->timestamp('paid_at')->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn(['status_pembayaran', 'payment_url', 'payment_token', 'payment_method', 'paid_at']);
        });
    }
};
