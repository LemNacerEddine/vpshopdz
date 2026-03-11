<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Platform Data
            SubscriptionPlanSeeder::class,

            // Themes
            ThemeSeeder::class,

            // Shipping Data (Algeria)
            WilayaSeeder::class,
            CommuneSeeder::class,
            ShippingCompanySeeder::class,

            // Admin User
            AdminSeeder::class,
        ]);
    }
}
