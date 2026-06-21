<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@disdik.aceh.go.id'],
            [
                'name' => 'Administrator',
                'password' => 'password',
                'phone' => '628123456789',
                'nip' => '199001012020011001',
            ]
        );

        $admin->assignRole('admin');
    }
}
