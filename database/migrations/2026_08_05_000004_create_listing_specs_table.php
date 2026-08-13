<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_specs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->string('spec_key');   // e.g. "Processor", "RAM", "Max Outlets", "Integrations"
            $table->string('spec_value'); // e.g. "Intel Core i5-1235U", "8GB DDR4", "Unlimited"
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('listing_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_specs');
    }
};
