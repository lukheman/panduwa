<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use App\Models\Bendahara;
use App\Models\KepalaDesa;

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

        $this->call([
            KategoriTransaksiSeeder::class,
            KegiatanSeeder::class,
            PemasukanSeeder::class,
            PengeluaranSeeder::class,
            InventarisSeeder::class,

        ]);
    }
}
