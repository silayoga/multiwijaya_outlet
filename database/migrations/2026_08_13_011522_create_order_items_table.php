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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();

            // Nullable + nullOnDelete, unlike cart_items' cascadeOnDelete — an
            // order is a historical record and must survive a listing/plan
            // being deleted later. The snapshot columns below are what
            // actually preserve the purchased details either way.
            $table->foreignId('listing_id')->nullable()->constrained('listings')->nullOnDelete();
            $table->foreignId('pricing_plan_id')->nullable()->constrained('pricing_plans')->nullOnDelete();

            $table->string('listing_name_snapshot');
            $table->string('plan_name_snapshot');
            $table->decimal('unit_price_snapshot', 14, 2)->nullable();
            $table->enum('billing_cycle_snapshot', ['one_time', 'monthly', 'yearly', 'custom_quote']);

            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
