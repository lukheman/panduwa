<?php

use App\Models\BidangKegiatan;
use App\Models\SubBidangKegiatan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->foreignId('sub_bidang_kegiatan_id')
                ->nullable()
                ->after('lokasi')
                ->constrained('sub_bidang_kegiatan')
                ->nullOnDelete();
        });

        if (! Schema::hasColumn('kegiatan', 'kelompok')) {
            return;
        }

        $mapping = [
            'bidang_pembangunan_desa' => ['kode_bidang' => '02', 'kode_sub' => '02.00', 'nama_sub' => 'Lainnya Pembangunan Desa'],
            'bidang_pemberdayaan_masyarakat' => ['kode_bidang' => '04', 'kode_sub' => '04.00', 'nama_sub' => 'Lainnya Pemberdayaan Masyarakat'],
            'bidang_pembinaan_kemasyarakatan' => ['kode_bidang' => '03', 'kode_sub' => '03.00', 'nama_sub' => 'Lainnya Pembinaan Kemasyarakatan'],
            'bidang_penanggulangan_bencana' => ['kode_bidang' => '05', 'kode_sub' => '05.00', 'nama_sub' => 'Lainnya Penanggulangan Bencana'],
        ];

        $namaBidangFallback = [
            '02' => 'Pelaksanaan Pembangunan Desa',
            '03' => 'Pembinaan Kemasyarakatan Desa',
            '04' => 'Pemberdayaan Masyarakat Desa',
            '05' => 'Penanggulangan Bencana, Keadaan Darurat dan Mendesak Desa',
        ];

        $kelompokExist = DB::table('kegiatan')->select('kelompok')->distinct()->pluck('kelompok');

        foreach ($kelompokExist as $kelompok) {
            if (! isset($mapping[$kelompok])) {
                continue;
            }

            $kodeBidang = $mapping[$kelompok]['kode_bidang'];

            $bidang = BidangKegiatan::firstOrCreate(
                ['kode' => $kodeBidang],
                ['nama' => $namaBidangFallback[$kodeBidang] ?? $kodeBidang]
            );

            $sub = SubBidangKegiatan::firstOrCreate(
                ['bidang_kegiatan_id' => $bidang->id, 'kode' => $mapping[$kelompok]['kode_sub']],
                ['nama' => $mapping[$kelompok]['nama_sub']]
            );

            DB::table('kegiatan')
                ->where('kelompok', $kelompok)
                ->update(['sub_bidang_kegiatan_id' => $sub->id]);
        }

        Schema::table('kegiatan', function (Blueprint $table) {
            $table->dropColumn('kelompok');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('kegiatan', 'kelompok')) {
            Schema::table('kegiatan', function (Blueprint $table) {
                $table->string('kelompok')->nullable()->after('lokasi');
            });
        }

        Schema::table('kegiatan', function (Blueprint $table) {
            if (Schema::hasColumn('kegiatan', 'sub_bidang_kegiatan_id')) {
                $table->dropConstrainedForeignId('sub_bidang_kegiatan_id');
            }
        });
    }
};
