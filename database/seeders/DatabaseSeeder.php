<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\ItemUnit;
use App\Models\PricingRule;
use App\Enums\ItemUnitStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Katalog Utama (54 barang + unit + foto) - dijalankan duluan
        $this->call(CatalogSeeder::class);

        // 1. Users (idempotent: jika email sudah ada, update data sisanya)
        User::updateOrCreate(
            ['email' => 'admin@summitgear.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'pin' => '123456',
                'phone' => '081100000001'
            ]
        );
        User::updateOrCreate(
            ['email' => 'kasir@summitgear.com'],
            [
                'name' => 'Kasir Utama',
                'password' => Hash::make('password123'),
                'role' => 'kasir',
                'pin' => '111111',
                'phone' => '081100000002'
            ]
        );
        User::updateOrCreate(
            ['email' => 'gudang@summitgear.com'],
            [
                'name' => 'Staf Gudang',
                'password' => Hash::make('password123'),
                'role' => 'gudang',
                'pin' => '222222',
                'phone' => '081100000003'
            ]
        );

        // 2. Customers (idempotent: unik berdasarkan phone)
        $customer = Customer::firstOrCreate(
            ['phone' => '081234567890'],
            [
                'name' => 'John Doe',
                'nik' => '3201010101010001',
            ]
        );

        // 3. Inventory Items & Units (idempotent: unik berdasarkan SKU)
        $tenda = InventoryItem::firstOrCreate(
            ['sku' => 'TND-001'],
            [
                'name' => 'Tenda Dome 4 Orang',
                'category' => 'Tenda',
                'is_package' => false,
                'rental_type' => 'daily_24h'
            ]
        );

        PricingRule::firstOrCreate(
            ['item_id' => $tenda->id, 'day_type' => 'weekday'],
            ['price_multiplier' => 1.00]
        );
        PricingRule::firstOrCreate(
            ['item_id' => $tenda->id, 'day_type' => 'weekend'],
            ['price_multiplier' => 1.20]
        );

        // Buat 5 unit Tenda, skip jika serial_number sudah ada
        for ($i = 1; $i <= 5; $i++) {
            ItemUnit::firstOrCreate(
                ['serial_number' => 'TND-001-' . str_pad($i, 3, '0', STR_PAD_LEFT)],
                [
                    'item_id' => $tenda->id,
                    'status' => ItemUnitStatus::AVAILABLE->value,
                    'replacement_value' => 750000
                ]
            );
        }
        
        $carrier = InventoryItem::firstOrCreate(
            ['sku' => 'CRR-001'],
            [
                'name' => 'Carrier 60L Osprey',
                'category' => 'Tas',
                'is_package' => false,
                'rental_type' => 'daily_24h'
            ]
        );
        
        PricingRule::firstOrCreate(
            ['item_id' => $carrier->id, 'day_type' => 'weekday'],
            ['price_multiplier' => 1.00]
        );

        for ($i = 1; $i <= 3; $i++) {
            ItemUnit::firstOrCreate(
                ['serial_number' => 'CRR-001-' . str_pad($i, 3, '0', STR_PAD_LEFT)],
                [
                    'item_id' => $carrier->id,
                    'status' => ItemUnitStatus::AVAILABLE->value,
                    'replacement_value' => 1500000
                ]
            );
        }
        
        // Settings (idempotent)
        \App\Models\Setting::firstOrCreate(
            ['key' => 'booking_window_days'],
            ['value' => '7']
        );
    }
}
