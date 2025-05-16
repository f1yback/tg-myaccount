<?php

declare(strict_types=1);

namespace App\Service\Telegram;

use App\DTO\Telegram\TelegramUpdateDTO;
use App\Entity\TelegramUpdateType;
use App\Service\Telegram\Command\BotCommandFactory;
use Psr\Log\LoggerInterface;

class TelegramBotHandler implements TelegramBotHandlerInterface
{
    public function __construct(
        private readonly BotCommandFactory $commandFactory,
        private readonly LoggerInterface $logger,
    ) {
    }

    /** @param array<array-key, mixed> $update */
    public function handleUpdate(array $update): void
    {
        $updateId = $update['update_id'] ?? 'unknown';

        $updateType = TelegramUpdateType::fromUpdateArray($update);

        $this->logger->info('TelegramBotHandler::handleUpdate - Processing update', [
            'update_id' => $updateId,
            'update_type' => $updateType->value,
        ]);

        try {
            $updateDTO = TelegramUpdateDTO::fromArray($update);
            $command = $this->commandFactory->getCommandForUpdate($updateDTO);

            if (null !== $command) {
                $this->logger->debug('TelegramBotHandler::handleUpdate - Command found', [
                    'update_id' => $updateId,
                    'command_class' => $command::class,
                ]);
                $command->execute($updateDTO);
            } else {
                $this->logger->warning('TelegramBotHandler::handleUpdate - No command found for update', [
                    'update_id' => $updateId,
                    'update_type' => $updateType->value,
                ]);
            }

            $this->logger->info('TelegramBotHandler::handleUpdate - Update processed successfully', [
                'update_id' => $updateId,
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('TelegramBotHandler::handleUpdate - Failed to process update', [
                'update_id' => $updateId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /* @param array<array-key, mixed> $update */
}
