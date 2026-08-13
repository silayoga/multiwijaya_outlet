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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            // No cascadeOnDelete — a payment is a financial record and must
            // survive independently of the order row's own lifecycle.
            $table->foreignId('order_id')->constrained('orders');
            $table->string('gateway');
            $table->string('gateway_reference')->unique();
            $table->enum('status', ['pending', 'paid', 'failed', 'expired', 'cancelled'])->default('pending');
            $table->decimal('amount', 14, 2);
            $table->json('raw_response')->nullable();
            $table->timestamps();

            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
