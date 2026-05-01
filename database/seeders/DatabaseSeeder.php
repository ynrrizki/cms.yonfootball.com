<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Aman dipanggil berulang (deploy): admin pakai firstOrCreate, produk pakai updateOrCreate.
     */
    public function run(): void
    {
        $adminEmail = config('app.seed_admin_email', 'super_admin@gmail.com');

        User::query()->firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Kecoak Ganteng',
                'password' => config('app.seed_admin_password', 'password'),
                'role' => Role::SUPER_ADMIN,
                'email_verified_at' => now(),
            ],
        );

        $this->call([
            ProductCatalogSeeder::class,
            ProductVariantDummySeeder::class,
        ]);
    }
}
