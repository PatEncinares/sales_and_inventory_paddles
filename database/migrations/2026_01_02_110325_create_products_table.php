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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // 🔑 Identity
            $table->string('name'); // e.g. Volley V1 Pickleball Paddle
            $table->string('sku')->nullable()->unique(); // VOL-V1-BLK
            $table->foreignId('brand_id')
                ->constrained()
                ->cascadeOnDelete();

            // 🎨 Variant-like attributes (MVP-friendly)
            $table->string('color')->nullable(); // Black, Red, Blue

            // 💰 Pricing
            $table->decimal('price', 10, 2)->nullable();  // selling price
            $table->decimal('cost', 10, 2)->nullable(); // optional but smart

            // 📦 Inventory
            $table->integer('stock_qty')->default(0);

            // 📝 Description / specs
            $table->text('description')->nullable();

            // ⚙️ Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // 🔍 Indexes (performance + sanity)
            $table->index(['name']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
