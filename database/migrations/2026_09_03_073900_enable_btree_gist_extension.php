<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Aktifkan ekstensi btree_gist jika belum ada di PostgreSQL
        if (config('database.default') === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist;');
        }
    }

    public function down(): void
    {
        //
    }
};
