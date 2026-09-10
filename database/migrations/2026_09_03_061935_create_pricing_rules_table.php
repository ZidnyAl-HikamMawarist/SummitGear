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
        Schema::create('pricing_rules', function (Blueprint $table) {

            $table->id();
            $table->foreignId('item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->string('day_type');
            $table->decimal('price_multiplier', 4, 2)->default(1.00);
            $table->timestamps();
    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
