<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait HasProfilePhoto
{
    public function hasAvatar(): bool
    {
        return !empty($this->avatar);
    }

    public function avatarUrl(): string
    {
        if ($this->hasAvatar()) {
            return Storage::url($this->avatar);
        }
        return '';
    }

    public function initials(): string
    {
        $words = explode(' ', $this->nama ?? '');
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($this->nama ?? 'U', 0, 2));
    }
}
