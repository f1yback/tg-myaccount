<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Telegram\Command;

use App\DTO\Telegram\TelegramCallbackQueryDTO;
use App\DTO\Telegram\TelegramMessageDTO;
use App\DTO\Telegram\TelegramUpdateDTO;
use App\DTO\Telegram\TelegramUserDTO;
use App\Service\Telegram\Command\Callback\AbstractCallbackCommand;
use App\Service\Telegram\Command\Callback\CallbackCommandFactory;
use App\Service\Telegram\Command\CallbackQueryCommand;
use App\Service\Telegram\TelegramBotApiInterface;
use App\Service\Telegram\TranslationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CallbackQueryCommandTest extends TestCase
{
    private TelegramBotApiInterface&MockObject $telegramBotApi;
    private TranslationService&MockObject $translationService;
    private CallbackCommandFactory&MockObject $callbackCommandFactory;
    private LoggerInterface&MockObject $logger;
    private CallbackQueryCommand $command;

    protected function setUp(): void
    {
        $this->telegramBotApi = $this->createMock(TelegramBotApiInterface::class);
        $this->translationService = $this->createMock(TranslationService::class);
        $this->callbackCommandFactory = $this->createMock(CallbackCommandFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->command = new CallbackQueryCommand(
            $this->telegramBotApi,
            $this->translationService,
            $this->callbackCommandFactory,
            $this->logger
        );
    }

    /**
     * @dataProvider supportsProvider
     */
    public function testSupports(bool $hasCallbackQuery, bool $expected): void
    {
        $update = $this->createUpdate($hasCallbackQuery);
        $this->assertEquals($expected, $this->command->supports($update));
    }

    public static function supportsProvider(): array
    {
        return [
            'with callback query' => [true, true],
            'without callback query' => [false, false],
        ];
    }

    public function testExecuteWithCommandFound(): void
    {
        $callbackQuery = $this->createCallbackQuery();
        $update = new TelegramUpdateDTO(123, null, $callbackQuery);

        $mockCommand = $this->createMock(AbstractCallbackCommand::class);

        $this->callbackCommandFactory
            ->expects($this->once())
            ->method('getCommandForCallback')
            ->with($callbackQuery)
            ->willReturn($mockCommand);

        $mockCommand
            ->expects($this->once())
            ->method('execute')
            ->with($callbackQuery);

        $this->command->execute($update);
    }

    public function testExecuteWithNoCommandFound(): void
    {
        $callbackQuery = $this->createCallbackQuery();
        $update = new TelegramUpdateDTO(123, null, $callbackQuery);

        $this->callbackCommandFactory
            ->expects($this->once())
            ->method('getCommandForCallback')
            ->with($callbackQuery)
            ->willReturn(null);

        $this->command->execute($update);
    }

    private function createUpdate(bool $withCallbackQuery = false): TelegramUpdateDTO
    {
        if ($withCallbackQuery) {
            return new TelegramUpdateDTO(123, null, $this->createCallbackQuery());
        }

        return new TelegramUpdateDTO(123, null, null);
    }

    private function createCallbackQuery(): TelegramCallbackQueryDTO
    {
        $user = new TelegramUserDTO(789, 'testuser', 'Test', 'Doe', 'en');
        $message = new TelegramMessageDTO(
            456,
            101112,
            $user,
            '/start',
            new \DateTimeImmutable('@1640995200')
        );

        return new TelegramCallbackQueryDTO(
            'callback_123',
            $user,
            'free_trial',
            $message
        );
    }
}
