<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // Row 1 - Tenda
            ['name' => 'Tenda Kap 2 Org', 'price_per_day' => 30000, 'category' => 'Tenda'],
            ['name' => 'Tenda Kap 4 Org', 'price_per_day' => 40000, 'category' => 'Tenda'],
            ['name' => 'Tenda Kap 5 Org', 'price_per_day' => 50000, 'category' => 'Tenda'],
            ['name' => 'Tenda Kap 6 Org', 'price_per_day' => 70000, 'category' => 'Tenda'],
            ['name' => 'Flysheet', 'price_per_day' => 10000, 'category' => 'Tenda'],
            ['name' => 'Pasak (5Pcs)', 'price_per_day' => 5000, 'category' => 'Tenda'],

            // Row 2 - Tidur & Tenda
            ['name' => 'Matras lipat', 'price_per_day' => 15000, 'category' => 'Tidur'],
            ['name' => 'Bantal Tiup', 'price_per_day' => 5000, 'category' => 'Tidur'],
            ['name' => 'Sleeping Bag', 'price_per_day' => 10000, 'category' => 'Tidur'],
            ['name' => 'Hammock', 'price_per_day' => 5000, 'category' => 'Tidur'],
            ['name' => 'Tiang Flysheet', 'price_per_day' => 10000, 'category' => 'Tenda'],
            ['name' => 'Tali Flysheet (6Pcs)', 'price_per_day' => 5000, 'category' => 'Tenda'],

            // Row 3 - Tidur & Masak
            ['name' => 'Matras Foil', 'price_per_day' => 5000, 'category' => 'Tidur'],
            ['name' => 'Matras Spons', 'price_per_day' => 5000, 'category' => 'Tidur'],
            ['name' => 'Nesting', 'price_per_day' => 10000, 'category' => 'Masak'],
            ['name' => 'Cooking Set Ds-200', 'price_per_day' => 10000, 'category' => 'Masak'],
            ['name' => 'Cooking Set Ds-308', 'price_per_day' => 15000, 'category' => 'Masak'],
            ['name' => 'Egg Holder', 'price_per_day' => 5000, 'category' => 'Masak'],

            // Row 4 - Masak
            ['name' => 'Kompor Koper', 'price_per_day' => 20000, 'category' => 'Masak'],
            ['name' => 'Grill / Panggangan', 'price_per_day' => 10000, 'category' => 'Masak'],
            ['name' => 'Kompor Windproof', 'price_per_day' => 10000, 'category' => 'Masak'],
            ['name' => 'Gas Portable', 'price_per_day' => 10000, 'category' => 'Masak'],
            ['name' => 'Sendok Lipat', 'price_per_day' => 5000, 'category' => 'Masak'],
            ['name' => 'Gelas', 'price_per_day' => 5000, 'category' => 'Masak'],

            // Row 5 - Aksesoris, Elektronik, Penerangan
            ['name' => 'Trekking Pole', 'price_per_day' => 10000, 'category' => 'Aksesoris'],
            ['name' => 'Powerbank', 'price_per_day' => 10000, 'category' => 'Elektronik'],
            ['name' => 'Headlamp', 'price_per_day' => 5000, 'category' => 'Penerangan'],
            ['name' => 'Lampu Tenda', 'price_per_day' => 5000, 'category' => 'Penerangan'],
            ['name' => 'Lentera', 'price_per_day' => 10000, 'category' => 'Penerangan'],
            ['name' => 'Jerigen Lipat', 'price_per_day' => 5000, 'category' => 'Aksesoris'],

            // Row 6 - Pakaian
            ['name' => 'Jaket Tipis', 'price_per_day' => 10000, 'category' => 'Pakaian'],
            ['name' => 'Jaket Tebal', 'price_per_day' => 15000, 'category' => 'Pakaian'],
            ['name' => 'Jaket Gorpcore', 'price_per_day' => 15000, 'category' => 'Pakaian'],
            ['name' => 'Baselayer', 'price_per_day' => 5000, 'category' => 'Pakaian'],
            ['name' => 'Vest / Rompi', 'price_per_day' => 10000, 'category' => 'Pakaian'],
            ['name' => 'Celana', 'price_per_day' => 10000, 'category' => 'Pakaian'],

            // Row 7 - Tas & Pakaian
            ['name' => 'Hydropack', 'price_per_day' => 10000, 'category' => 'Tas'],
            ['name' => 'Daypack 10L', 'price_per_day' => 10000, 'category' => 'Tas'],
            ['name' => 'Daypack 15L-25L', 'price_per_day' => 20000, 'category' => 'Tas'],
            ['name' => 'Carrier', 'price_per_day' => 30000, 'category' => 'Tas'],
            ['name' => 'Rain Cover Bag', 'price_per_day' => 5000, 'category' => 'Tas'],
            ['name' => 'Dry Fit', 'price_per_day' => 10000, 'category' => 'Pakaian'],

            // Row 8 - Furnitur & Pakaian
            ['name' => 'Kursi lipat', 'price_per_day' => 10000, 'category' => 'Furnitur'],
            ['name' => 'Kursi Lipat Alloy', 'price_per_day' => 15000, 'category' => 'Furnitur'],
            ['name' => 'Sarung Tangan', 'price_per_day' => 5000, 'category' => 'Pakaian'],
            ['name' => 'Topi Rimba', 'price_per_day' => 5000, 'category' => 'Pakaian'],
            ['name' => 'Topi', 'price_per_day' => 5000, 'category' => 'Pakaian'],
            ['name' => 'Celana Cargo Serut', 'price_per_day' => 10000, 'category' => 'Pakaian'],

            // Row 9 - Sepatu, Furnitur, Elektronik
            ['name' => 'Sepatu', 'price_per_day' => 30000, 'category' => 'Sepatu'],
            ['name' => 'Gaiter', 'price_per_day' => 10000, 'category' => 'Sepatu'],
            ['name' => 'Kacamata', 'price_per_day' => 5000, 'category' => 'Aksesoris'],
            ['name' => 'Meja Lipat', 'price_per_day' => 15000, 'category' => 'Furnitur'],
            ['name' => 'Meja Lipat Panjang', 'price_per_day' => 30000, 'category' => 'Furnitur'],
            ['name' => 'Tripod + Remote', 'price_per_day' => 10000, 'category' => 'Elektronik'],
        ];

        foreach ($items as $index => $item) {
            $sku = 'ITM-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            // generate clean filename from name (persis seperti format semula)
            $filename = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $item['name'])) . '.jpg';
            $photoUrl = 'images/catalog/' . $filename;

            $model = \App\Models\InventoryItem::updateOrCreate(
                ['sku' => $sku],
                [
                    'name' => $item['name'],
                    'category' => $item['category'],
                    'price_per_day' => $item['price_per_day'],
                    'photo_url' => $photoUrl,
                    'rental_type' => 'daily_24h',
                    'is_package' => false,
                ]
            );

            // Buat default pricing rule (weekday 1.00, weekend 1.15) per item jika belum ada
            \App\Models\PricingRule::firstOrCreate(
                ['item_id' => $model->id, 'day_type' => 'weekday'],
                ['price_multiplier' => 1.00]
            );
            \App\Models\PricingRule::firstOrCreate(
                ['item_id' => $model->id, 'day_type' => 'weekend'],
                ['price_multiplier' => 1.15]
            );

            // Buat 5 unit per item jika serial_number belum ada
            for ($i = 1; $i <= 5; $i++) {
                \App\Models\ItemUnit::firstOrCreate(
                    ['serial_number' => $sku . '-' . str_pad($i, 2, '0', STR_PAD_LEFT)],
                    [
                        'item_id' => $model->id,
                        'status' => 'Available',
                        'replacement_value' => (int) ($item['price_per_day'] * 10),
                    ]
                );
            }
        }
    }
}
