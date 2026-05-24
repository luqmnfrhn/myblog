<?php

namespace App\Enums;

enum ReactionType: string
{
    case ThoughtProvoking = 'thought_provoking';
    case BeautifullyWritten = 'beautifully_written';
    case ChangedMyMind = 'changed_my_mind';

    public function label(): string
    {
        return match ($this) {
            self::ThoughtProvoking => 'Thought-provoking',
            self::BeautifullyWritten => 'Beautifully written',
            self::ChangedMyMind => 'Changed my mind',
        };
    }
}
