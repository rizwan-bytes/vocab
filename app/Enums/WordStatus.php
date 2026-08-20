<?php

namespace App\Enums;

enum WordStatus: string
{
    case New = 'new';
    case Learning = 'learning';
    case Reviewing = 'reviewing';
    case Mastered = 'mastered';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Learning => 'Learning',
            self::Reviewing => 'Reviewing',
            self::Mastered => 'Mastered',
        };
    }

    /** Tailwind color token used consistently across badges/charts for this status. */
    public function color(): string
    {
        return match ($this) {
            self::New => 'slate',
            self::Learning => 'amber',
            self::Reviewing => 'blue',
            self::Mastered => 'emerald',
        };
    }
}
