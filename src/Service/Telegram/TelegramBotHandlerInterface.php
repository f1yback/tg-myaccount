<?php

declare(strict_types=1);

namespace App\Service\Telegram;

interface TelegramBotHandlerInterface
{
    /** @param array<array-key, mixed> $update */
    public function handleUpdate(array $update): void;
}
