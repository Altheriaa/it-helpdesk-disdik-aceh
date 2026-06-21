<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = [
            ['name' => 'Sekretariat', 'description' => 'Unit sekretariat dinas'],
            ['name' => 'Bidang Pembinaan Sekolah Dasar', 'description' => 'Pembinaan SD di Provinsi Aceh'],
            ['name' => 'Bidang Pembinaan Sekolah Menengah Pertama', 'description' => 'Pembinaan SMP di Provinsi Aceh'],
            ['name' => 'Bidang Pembinaan Sekolah Menengah Atas', 'description' => 'Pembinaan SMA di Provinsi Aceh'],
            ['name' => 'Bidang Pembinaan Sekolah Menengah Kejuruan', 'description' => 'Pembinaan SMK di Provinsi Aceh'],
            ['name' => 'Bidang Pembinaan Pendidikan Khusus', 'description' => 'Pembinaan pendidikan khusus dan layanan khusus'],
            ['name' => 'Bidang Pembinaan Ketenagaan', 'description' => 'Pembinaan tenaga pendidik dan kependidikan'],
            ['name' => 'Bidang Pengelolaan Keuangan dan Aset', 'description' => 'Pengelolaan keuangan dan aset dinas'],
            ['name' => 'Unit Pelaksana Teknis (UPT)', 'description' => 'Unit pelaksana teknis daerah'],
            ['name' => 'Bagian Umum dan Kepegawaian', 'description' => 'Urusan umum dan kepegawaian'],
            ['name' => 'Sub Bagian Perencanaan dan Program', 'description' => 'Perencanaan dan program kerja dinas'],
        ];

        foreach ($divisions as $division) {
            Division::firstOrCreate(['name' => $division['name']], $division);
        }
    }
}
