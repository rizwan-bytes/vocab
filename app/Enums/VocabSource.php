<?php

namespace App\Enums;

enum VocabSource: string
{
    case Book = 'book';
    case Ielts = 'ielts';
    case Article = 'article';
    case YouTube = 'youtube';
    case Conversation = 'conversation';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Book => 'Book',
            self::Ielts => 'IELTS Material',
            self::Article => 'Article',
            self::YouTube => 'YouTube',
            self::Conversation => 'Conversation',
            self::Other => 'Other',
        };
    }
}
