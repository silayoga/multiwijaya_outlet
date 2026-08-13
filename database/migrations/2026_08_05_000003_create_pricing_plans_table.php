<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('listings')->cascadeOnDelete();

            // For physical_product this is usually just one row: plan_name = "Retail".
            // For software_service this can be many: "Starter" / "Pro" / "Enterprise".
            $table->string('plan_name')->default('Retail');

            $table->enum('billing_cycle', ['one_time', 'monthly', 'yearly', 'custom_quote'])->default('one_time');

            // Nullable: custom_quote plans have no fixed price (handled via CTA, not cart price).
            $table->decimal('price', 14, 2)->nullable();
            $table->string('currency', 3)->default('IDR');

            $table->json('features')->nullable(); // short bullet list, mainly for software tiers
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['listing_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_plans');
    }
};
