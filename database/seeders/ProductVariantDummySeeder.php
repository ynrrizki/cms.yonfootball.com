<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductVariantDummySeeder extends Seeder
{
    public function run(): void
    {
        $packagesByProduct = [
            'EFOOTBALL' => [
                ['name' => '130 Coins', 'price' => 19000],
                ['name' => '550 Coins', 'price' => 72000],
                ['name' => '1040 Coins', 'price' => 139000],
                ['name' => '2130 Coins', 'price' => 275000],
            ],
            'FCMOBILE' => [
                ['name' => '40 FC Points', 'price' => 7000],
                ['name' => '100 FC Points', 'price' => 16000],
                ['name' => '520 FC Points', 'price' => 79000],
                ['name' => '1070 FC Points', 'price' => 155000],
            ],
            'ML' => [
                ['name' => '86 Diamonds', 'price' => 22000],
                ['name' => '172 Diamonds', 'price' => 43000],
                ['name' => '257 Diamonds', 'price' => 64000],
                ['name' => '706 Diamonds', 'price' => 170000],
            ],
            'PUBG' => [
                ['name' => '60 UC', 'price' => 17000],
                ['name' => '325 UC', 'price' => 76000],
                ['name' => '660 UC', 'price' => 149000],
                ['name' => '1800 UC', 'price' => 385000],
            ],
            'GENSHIN' => [
                ['name' => '60 Genesis Crystals', 'price' => 16000],
                ['name' => '300+30 Genesis Crystals', 'price' => 79000],
                ['name' => '980+110 Genesis Crystals', 'price' => 235000],
                ['name' => '1980+260 Genesis Crystals', 'price' => 469000],
            ],
            'VALORANT' => [
                ['name' => '420 VP', 'price' => 50000],
                ['name' => '700 VP', 'price' => 79000],
                ['name' => '1375 VP', 'price' => 149000],
                ['name' => '2400 VP', 'price' => 249000],
            ],
            'ROBLOX' => [
                ['name' => '80 Robux', 'price' => 17000],
                ['name' => '400 Robux', 'price' => 75000],
                ['name' => '800 Robux', 'price' => 149000],
                ['name' => '1700 Robux', 'price' => 299000],
            ],
            'COC' => [
                ['name' => '80 Gems', 'price' => 15000],
                ['name' => '500 Gems', 'price' => 79000],
                ['name' => '1200 Gems', 'price' => 149000],
                ['name' => '2500 Gems', 'price' => 299000],
            ],
            'FREE FIRE' => [
                ['name' => '70 Diamonds', 'price' => 10000],
                ['name' => '140 Diamonds', 'price' => 19000],
                ['name' => '355 Diamonds', 'price' => 47000],
                ['name' => '720 Diamonds', 'price' => 93000],
            ],
            'CLASH ROYALE' => [
                ['name' => '80 Gems', 'price' => 15000],
                ['name' => '500 Gems', 'price' => 79000],
                ['name' => '1200 Gems', 'price' => 149000],
                ['name' => '2500 Gems', 'price' => 299000],
            ],
            'GROWTOPIA' => [
                ['name' => '50,000 WL', 'price' => 12000],
                ['name' => '100,000 WL', 'price' => 23000],
                ['name' => '250,000 WL', 'price' => 56000],
                ['name' => '500,000 WL', 'price' => 109000],
            ],
            'HOK' => [
                ['name' => '80 Tokens', 'price' => 15000],
                ['name' => '240 Tokens', 'price' => 42000],
                ['name' => '400 Tokens', 'price' => 68000],
                ['name' => '800 Tokens', 'price' => 132000],
            ],
            'POINT BLANK' => [
                ['name' => '1,200 Cash', 'price' => 12000],
                ['name' => '2,400 Cash', 'price' => 23000],
                ['name' => '6,000 Cash', 'price' => 56000],
                ['name' => '12,000 Cash', 'price' => 109000],
            ],
            'NETFLIX' => [
                ['name' => 'Mobile 1 Bulan', 'price' => 59000],
                ['name' => 'Basic 1 Bulan', 'price' => 65000],
                ['name' => 'Standard 1 Bulan', 'price' => 120000],
                ['name' => 'Premium 1 Bulan', 'price' => 186000],
            ],
            'MAGIC CHESS' => [
                ['name' => '86 Diamonds', 'price' => 22000],
                ['name' => '172 Diamonds', 'price' => 43000],
                ['name' => '257 Diamonds', 'price' => 64000],
                ['name' => '706 Diamonds', 'price' => 170000],
            ],
            'BLOOD STRIKE' => [
                ['name' => '100 Gold', 'price' => 16000],
                ['name' => '300 Gold', 'price' => 45000],
                ['name' => '680 Gold', 'price' => 96000],
                ['name' => '1280 Gold', 'price' => 175000],
            ],
        ];

        Product::query()
            ->orderBy('id')
            ->get()
            ->each(function (Product $product) use ($packagesByProduct): void {
                $packages = $packagesByProduct[$product->name] ?? [
                    ['name' => 'Paket Hemat', 'price' => 15000],
                    ['name' => 'Paket Standar', 'price' => 45000],
                    ['name' => 'Paket Pro', 'price' => 95000],
                    ['name' => 'Paket Sultan', 'price' => 175000],
                ];

                foreach ($packages as $sortOrder => $package) {
                    $price = (int) $package['price'];
                    $priceOriginal = (int) round($price * 1.08);
                    $priceDiscount = (int) max(0, $priceOriginal - $price);

                    ProductVariant::query()->updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'name' => $package['name'],
                        ],
                        [
                            'leading_url' => null,
                            'price_original' => $priceOriginal,
                            'price' => $price,
                            'price_discount' => $priceDiscount,
                            'is_active' => true,
                        ]
                    );
                }
            });
    }
}
