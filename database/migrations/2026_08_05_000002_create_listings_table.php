<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('sku')->unique()->nullable(); // physical goods mostly; software may skip
            $table->string('name');
            $table->string('slug')->unique();

            // The one field that tells the rest of the system how to treat this listing.
            $table->enum('listing_type', ['physical_product', 'software_service']);

            $table->string('short_description', 280)->nullable();
            $table->longText('long_description')->nullable();
            $table->json('images')->nullable(); // array of media paths/URLs, first = primary

            $table->enum('status', ['draft', 'active', 'discontinued'])->default('draft');
            $table->boolean('is_featured')->default(false);

            // Only meaningful for physical_product; ignored/null for software_service.
            $table->integer('stock_qty')->nullable();
            $table->boolean('track_stock')->default(false);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['listing_type', 'status']);
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
