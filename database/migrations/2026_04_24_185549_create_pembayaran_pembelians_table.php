<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pembayaran_pembelians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembelian_id')->constrained('pembelians')->onDelete('cascade');
            $table->date('tgl_bayar');
            $table->string('jenis_pembayaran');
            $table->decimal('gross_amount', 15, 2);
            $table->string('order_id')->unique();
            $table->string('payment_type')->nullable();
            $table->string('status_code')->nullable();
            $table->integer('transaction_id')->nullable();
            $table->datetime('transaction_time')->nullable();
            $table->datetime('settlement_time')->nullable();
            $table->string('status_message')->nullable();
            $table->string('merchant_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_pembelians');
    }
};
