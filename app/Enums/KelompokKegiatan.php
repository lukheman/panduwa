<?php

namespace App\Enums;

enum KelompokKegiatan: string
{
    case BIDANG_PEMBANGUNAN_DESA = 'bidang_pembangunan_desa';
    case BIDANG_PEMBERDAYAAN_MASYARAKAT = 'bidang_pemberdayaan_masyarakat';
    case BIDANG_PEMBINAAN_KEMASYARAKATAN = 'bidang_pembinaan_kemasyarakatan';
    case BIDANG_PENANGGULANGAN_BENCANA = 'bidang_penanggulangan_bencana';

    public function getLabel(): string
    {
        return match ($this) {
            self::BIDANG_PEMBANGUNAN_DESA => 'Bidang Pembangunan Desa',
            self::BIDANG_PEMBERDAYAAN_MASYARAKAT => 'Bidang Pemberdayaan Masyarakat',
            self::BIDANG_PEMBINAAN_KEMASYARAKATAN => 'Bidang Pembinaan Kemasyarakatan',
            self::BIDANG_PENANGGULANGAN_BENCANA => 'Bidang Penanggulangan Bencana & Keadaan Mendesa',
        };
    }

    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }
}
