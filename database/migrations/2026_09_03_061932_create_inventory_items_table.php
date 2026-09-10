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
        Schema::create('inventory_items', function (Blueprint $table) {

            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('category');
            $table->text('description')->nullable();
            $table->boolean('is_package')->default(false);
            $table->string('rental_type')->nullable(); // e.g., daily_24h
            $table->bigInteger('price_per_day')->default(0);
            $table->text('photo_url')->nullable();
            $table->timestamps();
    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
