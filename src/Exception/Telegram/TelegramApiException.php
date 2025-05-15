<?php

declare(strict_types=1);

namespace App\Exception\Telegram;

use App\Exception\BotException;

abstract class TelegramApiException extends BotException
{
}
