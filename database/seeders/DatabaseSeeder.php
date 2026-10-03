<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Akun Pengguna Terlebih Dahulu
        $this->call([
            UserSeeder::class,
        ]);

        // 2. Daftarkan Stok Gudang Nomor Surat (6000 lembar per lini produk)
        // INI WAJIB JALAN DULUAN agar seeder polis di bawahnya bisa ngambil nomor!
        $this->call([
            SuratBlockSeeder::class,
        ]);

        // 3. Panggil data dummy langsung masuk ke tabel Buku Besar masing-masing
        $this->call([
            AkdDummySeeder::class,
            ParDummySeeder::class,
            VehicleDummySeeder::class,
            VariaDummySeeder::class,
            PublicLiabilityDummySeeder::class,
            SuretyBondDummySeeder::class,
        ]);
    }
}