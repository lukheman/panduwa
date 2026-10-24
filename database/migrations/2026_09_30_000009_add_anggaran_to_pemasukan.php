<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemasukan', function (Blueprint $table) {
            $table->decimal('anggaran', 15, 2)->nullable()->after('jumlah');
        });

        DB::table('pemasukan')->whereNull('anggaran')->update([
            'anggaran' => DB::raw('jumlah'),
        ]);
    }

    public function down(): void
    {
        Schema::table('pemasukan', function (Blueprint $table) {
            $table->dropColumn('anggaran');
        });
    }
};
