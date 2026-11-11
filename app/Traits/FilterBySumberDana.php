<?php

namespace App\Traits;

use App\Models\SumberDana;
use Illuminate\Database\Eloquent\Builder;

trait FilterBySumberDana
{
    /**
     * Batasi query pada sumber dana tertentu. Data lama yang belum memiliki
     * sumber dana tetap dianggap sebagai Dana Desa agar tetap terhitung.
     */
    public function scopeUntukSumberDana(Builder $query, ?int $sumberDanaId): Builder
    {
        if (! $sumberDanaId) {
            return $query;
        }

        $kolom = $this->qualifyColumn('sumber_dana_id');
        $danaDesaId = (int) SumberDana::query()
            ->where('kode', SumberDana::KODE_DANA_DESA)
            ->value('id');

        return $query->where(function (Builder $query) use ($kolom, $sumberDanaId, $danaDesaId): void {
            $query->where($kolom, $sumberDanaId);

            if ($danaDesaId && $sumberDanaId === $danaDesaId) {
                $query->orWhereNull($kolom);
            }
        });
    }
}
