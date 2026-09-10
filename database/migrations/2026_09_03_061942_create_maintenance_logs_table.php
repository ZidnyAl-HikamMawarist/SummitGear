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
        Schema::create('maintenance_logs', function (Blueprint $table) {

            $table->id();
            $table->foreignId('item_unit_id')->constrained('item_units')->onDelete('cascade');
            $table->string('type');
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->string('technician_name')->nullable();
            $table->timestamps();
    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_logs');
    }
};
