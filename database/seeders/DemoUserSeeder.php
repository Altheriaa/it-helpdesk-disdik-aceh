<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Division;
use App\Models\Support;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = Division::all();

        // IT Support users
        $supportUsers = [
            [
                'name' => 'Ahmad Rizki',
                'email' => 'support@disdik.aceh.go.id',
                'phone' => '628111222333',
                'nip' => '199201012020011002',
            ],
            [
                'name' => 'Dian Saputra',
                'email' => 'support2@disdik.aceh.go.id',
                'phone' => '628111222444',
                'nip' => '199301012020011003',
            ],
        ];

        foreach ($supportUsers as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                array_merge($data, ['password' => 'password'])
            );

            $user->assignRole('it_support');

            Support::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'division_id' => $divisions->first()?->id,
                    'position' => 'Staf IT Support',
                ]
            );
        }

        // Pegawai users
        $pegawaiUsers = [
            [
                'name' => 'Siti Aminah',
                'email' => 'pegawai@disdik.aceh.go.id',
                'phone' => '628222333444',
                'nip' => '198501012010012001',
                'division' => 'Sekretariat',
                'position' => 'Staf Administrasi',
            ],
            [
                'name' => 'Muhammad Faisal',
                'email' => 'pegawai2@disdik.aceh.go.id',
                'phone' => '628222333555',
                'nip' => '198701012012011001',
                'division' => 'Bidang Pembinaan Sekolah Dasar',
                'position' => 'Kepala Seksi',
            ],
            [
                'name' => 'Cut Nurhaliza',
                'email' => 'pegawai3@disdik.aceh.go.id',
                'phone' => '628222333666',
                'nip' => '199001012015012001',
                'division' => 'Bidang Pembinaan Ketenagaan',
                'position' => 'Staf Bidang',
            ],
        ];

        foreach ($pegawaiUsers as $data) {
            $division = $divisions->firstWhere('name', $data['division']);

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => 'password',
                    'phone' => $data['phone'],
                    'nip' => $data['nip'],
                ]
            );

            $user->assignRole('pegawai');

            Client::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'division_id' => $division?->id ?? $divisions->first()?->id,
                    'position' => $data['position'],
                ]
            );
        }
    }
}
