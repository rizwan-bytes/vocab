<?php

namespace App\Enums;

enum TutorSessionType: string
{
    case Essay = 'essay';
    case Speaking = 'speaking';
    case VocabularyPractice = 'vocabulary_practice';

    public function label(): string
    {
        return match ($this) {
            self::Essay => 'Essay Feedback',
            self::Speaking => 'Speaking Practice',
            self::VocabularyPractice => 'Vocabulary Practice',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Essay => 'pencil',
            self::Speaking => 'sparkles',
            self::VocabularyPractice => 'book-open',
        };
    }
}
