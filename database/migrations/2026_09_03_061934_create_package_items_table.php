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
        Schema::create('package_items', function (Blueprint $table) {

            $table->id();
            $table->foreignId('package_id')->constrained('inventory_items')->onDelete('cascade');
            $table->foreignId('component_item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->unique(['package_id', 'component_item_id']);
            $table->timestamps();
    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_items');
    }
};
