<?php

namespace App\Enums;

enum StatusKegiatan: string
{
    case PERENCANAAN = 'perencanaan';
    case BERJALAN = 'berjalan';
    case SELESAI = 'selesai';

    public function getLabel(): string
    {
        return match ($this) {
            self::PERENCANAAN => 'Perencanaan',
            self::BERJALAN => 'Berjalan',
            self::SELESAI => 'Selesai',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PERENCANAAN => 'secondary',
            self::BERJALAN => 'primary',
            self::SELESAI => 'success',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PERENCANAAN => 'fas fa-calendar-alt',
            self::BERJALAN => 'fas fa-spinner',
            self::SELESAI => 'fas fa-check-circle',
        };
    }

    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }
}
