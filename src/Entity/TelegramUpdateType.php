<?php

declare(strict_types=1);

namespace App\Entity;

enum TelegramUpdateType: string
{
    case MESSAGE = 'message';
    case CALLBACK_QUERY = 'callback_query';
    case INLINE_QUERY = 'inline_query';
    case UNKNOWN = 'unknown';

    /** @param array<array-key, mixed> $update */
    public static function fromUpdateArray(array $update): self
    {
        if (isset($update['message'])) {
            return self::MESSAGE;
        }
        if (isset($update['callback_query'])) {
            return self::CALLBACK_QUERY;
        }
        if (isset($update['inline_query'])) {
            return self::INLINE_QUERY;
        }

        return self::UNKNOWN;
    }
}
