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
        // Kolom photo_url, address, source sudah ditambahkan langsung pada
        // create table (inventory_items, customers, rentals) agar terurut benar
        // dan tidak perlu ->after() yang rawan error jika kolom referensi tidak ada.

        \Illuminate\Support\Facades\DB::table('settings')->insertOrIgnore([
            'key' => 'online_booking_expire_hours',
            'value' => '24',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('settings')->where('key', 'online_booking_expire_hours')->delete();
    }
};
