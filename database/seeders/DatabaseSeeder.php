<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Bendahara;
use App\Models\KaurUmum;
use App\Models\KepalaDesa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        Admin::create([
            'nama' => 'Administrator Utama',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password123'),
        ]);

        Bendahara::create([
            'nama' => 'Budi Santoso',
            'email' => 'bendahara@gmail.com',
            'password' => Hash::make('password123'),
        ]);

        KepalaDesa::create([
            'nama' => 'H. Rahmat Hidayat',
            'email' => 'kepaladesa@gmail.com',
            'password' => Hash::make('password123'),
        ]);

        KaurUmum::create([
            'nama' => 'Siti Aminah',
            'email' => 'kaurumum@gmail.com',
            'password' => Hash::make('password123'),
        ]);

        $this->call([
            BidangSubBidangSeeder::class,
            KegiatanSeeder::class,
            PemasukanSeeder::class,
            PengeluaranSeeder::class,
            InventarisSeeder::class,

        ]);
    }
}
