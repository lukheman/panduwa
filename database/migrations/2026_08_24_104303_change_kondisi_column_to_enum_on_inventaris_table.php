<?php

use App\Enums\KondisiInventaris;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('inventaris')->select('id', 'kondisi')->get() as $row) {
            $normalized = strtolower(trim((string) $row->kondisi));

            if (! in_array($normalized, KondisiInventaris::values(), true)) {
                $normalized = KondisiInventaris::BAIK->value;
            }

            if ($normalized !== $row->kondisi) {
                DB::table('inventaris')->where('id', $row->id)->update(['kondisi' => $normalized]);
            }
        }

        Schema::table('inventaris', function (Blueprint $table) {
            $table->enum('kondisi', KondisiInventaris::values())->default(KondisiInventaris::BAIK->value)->change();
        });
    }

    public function down(): void
    {
        Schema::table('inventaris', function (Blueprint $table) {
            $table->string('kondisi')->change();
        });
    }
};
