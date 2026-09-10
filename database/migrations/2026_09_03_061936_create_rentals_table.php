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
        Schema::create('rentals', function (Blueprint $table) {

            $table->id();
            $table->string('rental_code')->unique();
            $table->foreignId('customer_id')->constrained('customers');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->dateTime('scheduled_return_time');
            $table->string('status');
            $table->bigInteger('total_price')->default(0);
            $table->bigInteger('discount')->default(0);
            $table->bigInteger('deposit_amount')->default(0);
            $table->string('source')->default('walk_in');
            $table->timestamps();
            $table->softDeletes();
    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
