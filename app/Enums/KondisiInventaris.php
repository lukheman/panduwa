<?php

namespace App\Enums;

enum KondisiInventaris: string
{
    case BAIK = 'baik';
    case RUSAK_RINGAN = 'rusak ringan';
    case RUSAK_BERAT = 'rusak berat';
    case HILANG = 'hilang';

    public function getLabel(): string
    {
        return match ($this) {
            self::BAIK => 'Baik',
            self::RUSAK_RINGAN => 'Rusak Ringan',
            self::RUSAK_BERAT => 'Rusak Berat',
            self::HILANG => 'Hilang',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::BAIK => 'success',
            self::RUSAK_RINGAN => 'warning',
            self::RUSAK_BERAT => 'danger',
            self::HILANG => 'secondary',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::BAIK => 'fas fa-check-circle',
            self::RUSAK_RINGAN => 'fas fa-exclamation-triangle',
            self::RUSAK_BERAT => 'fas fa-times-circle',
            self::HILANG => 'fas fa-question-circle',
        };
    }

    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }
}
