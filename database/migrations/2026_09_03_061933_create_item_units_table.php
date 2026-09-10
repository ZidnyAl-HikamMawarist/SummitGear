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
        Schema::create('item_units', function (Blueprint $table) {

            $table->id();
            $table->foreignId('item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->string('serial_number')->unique();
            $table->string('status');
            $table->text('condition_notes')->nullable();
            $table->bigInteger('replacement_value')->default(0);
            $table->timestamps();
            $table->softDeletes();
    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_units');
    }
};
