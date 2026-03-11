<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $exists = DB::table('users')->where('email', 'admin@vpshopdz.com')->exists();

        if (!$exists) {
            DB::table('users')->insert([
                'id' => Str::uuid()->toString(),
                'name' => 'Super Admin',
                'email' => 'admin@vpshopdz.com',
                'password' => Hash::make('VPShopDZ@2025'),
                'phone' => '0550000000',
                'role' => 'super_admin',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info('✅ Super Admin created: admin@vpshopdz.com / VPShopDZ@2025');
        } else {
            $this->command->info('ℹ️ Super Admin already exists');
        }
    }
}
