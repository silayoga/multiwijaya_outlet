<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('session_token')->nullable()->index(); // guest carts before login
            $table->enum('status', ['active', 'converted', 'abandoned'])->default('active');
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->foreignId('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->foreignId('pricing_plan_id')->constrained('pricing_plans')->cascadeOnDelete();

            // Snapshot fields so historical cart/order lines survive price changes later.
            $table->string('listing_name_snapshot');
            $table->string('plan_name_snapshot');
            $table->decimal('unit_price_snapshot', 14, 2)->nullable();
            $table->enum('billing_cycle_snapshot', ['one_time', 'monthly', 'yearly', 'custom_quote']);

            $table->unsignedInteger('quantity')->default(1); // typically 1 for software_service
            $table->timestamps();

            $table->unique(['cart_id', 'listing_id', 'pricing_plan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
