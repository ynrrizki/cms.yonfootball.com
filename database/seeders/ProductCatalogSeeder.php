<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $gameCategory = ProductCategory::query()->firstOrCreate(
            ['name' => 'Game Top Up'],
            ['sort_order' => 1]
        );

        $entertainmentCategory = ProductCategory::query()->firstOrCreate(
            ['name' => 'Entertainment'],
            ['sort_order' => 2]
        );

        $catalog = [
            ['name' => 'EFOOTBALL', 'category_id' => $gameCategory->id, 'inputs' => [
                'konami_id' => 'Konami ID / Email',
                'region' => 'Region',
            ]],
            ['name' => 'FCMOBILE', 'category_id' => $gameCategory->id, 'inputs' => [
                'uid' => 'User ID',
                'login_method' => 'Login Method (Guest/Facebook/Google)',
            ]],
            ['name' => 'ML', 'category_id' => $gameCategory->id, 'inputs' => [
                'user_id' => 'User ID',
                'zone_id' => 'Zone ID',
            ]],
            ['name' => 'PUBG', 'category_id' => $gameCategory->id, 'inputs' => [
                'pubg_id' => 'PUBG ID',
                'nickname' => 'Nickname',
            ]],
            ['name' => 'GENSHIN', 'category_id' => $gameCategory->id, 'inputs' => [
                'uid' => 'UID',
                'server' => 'Server (Asia/Europe/America/TW-HK-MO)',
            ]],
            ['name' => 'VALORANT', 'category_id' => $gameCategory->id, 'inputs' => [
                'riot_id' => 'Riot ID',
                'tagline' => 'Tagline',
                'region' => 'Region',
            ]],
            ['name' => 'ROBLOX', 'category_id' => $gameCategory->id, 'inputs' => [
                'username' => 'Username Roblox',
            ]],
            ['name' => 'COC', 'category_id' => $gameCategory->id, 'inputs' => [
                'player_tag' => 'Player Tag',
            ]],
            ['name' => 'FREE FIRE', 'category_id' => $gameCategory->id, 'inputs' => [
                'user_id' => 'User ID',
            ]],
            ['name' => 'CLASH ROYALE', 'category_id' => $gameCategory->id, 'inputs' => [
                'player_tag' => 'Player Tag',
            ]],
            ['name' => 'GROWTOPIA', 'category_id' => $gameCategory->id, 'inputs' => [
                'grow_id' => 'Grow ID',
                'world_name' => 'World Name',
            ]],
            ['name' => 'HOK', 'category_id' => $gameCategory->id, 'inputs' => [
                'player_id' => 'Player ID',
                'server' => 'Server',
            ]],
            ['name' => 'POINT BLANK', 'category_id' => $gameCategory->id, 'inputs' => [
                'pb_id' => 'Point Blank ID',
            ]],
            ['name' => 'NETFLIX', 'category_id' => $entertainmentCategory->id, 'inputs' => [
                'email' => 'Email Account',
                'pin_profile' => 'PIN Profile (optional)',
                'notes' => 'Notes / Request',
            ]],
            ['name' => 'MAGIC CHESS', 'category_id' => $gameCategory->id, 'inputs' => [
                'user_id' => 'User ID',
                'zone_id' => 'Zone ID',
            ]],
            ['name' => 'BLOOD STRIKE', 'category_id' => $gameCategory->id, 'inputs' => [
                'player_id' => 'Player ID',
                'server' => 'Server',
            ]],
        ];

        foreach ($catalog as $index => $item) {
            $name = $item['name'];

            Product::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'code' => sprintf('CAT-%03d', $index + 1),
                    'leading_url' => null,
                    'background_url' => null,
                    'category_id' => $item['category_id'],
                    'inputs' => $item['inputs'],
                    'is_active' => true,
                    'is_popular' => false,
                ]
            );
        }
    }
}
