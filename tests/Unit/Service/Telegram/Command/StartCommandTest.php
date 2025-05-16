<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Telegram\Command;

use App\DTO\Telegram\TelegramMessageDTO;
use App\DTO\Telegram\TelegramUpdateDTO;
use App\DTO\Telegram\TelegramUserDTO;
use App\Entity\User;
use App\Service\Telegram\Command\StartCommand;
use App\Service\Telegram\TelegramBotApiInterface;
use App\Service\Telegram\TranslationService;
use App\Service\User\UserManagerInterface;
use App\Service\Vpn\VpnConfigManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class StartCommandTest extends TestCase
{
    private TelegramBotApiInterface&MockObject $telegramBotApi;
    private TranslationService&MockObject $translationService;
    private UserManagerInterface&MockObject $userManager;
    private VpnConfigManagerInterface&MockObject $vpnConfigManager;
    private LoggerInterface&MockObject $logger;
    private StartCommand $command;

    protected function setUp(): void
    {
        $this->telegramBotApi = $this->createMock(TelegramBotApiInterface::class);
        $this->translationService = $this->createMock(TranslationService::class);
        $this->userManager = $this->createMock(UserManagerInterface::class);
        $this->vpnConfigManager = $this->createMock(VpnConfigManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->command = new StartCommand(
            $this->telegramBotApi,
            $this->translationService,
            $this->logger,
            $this->userManager,
            $this->vpnConfigManager
        );
    }

    /**
     * @dataProvider supportsProvider
     */
    public function testSupports(string $text, bool $expected): void
    {
        $update = $this->createUpdate($text);
        $this->assertEquals($expected, $this->command->supports($update));
    }

    public static function supportsProvider(): array
    {
        return [
            'start command' => ['/start', true],
            'start with params' => ['/start param1 param2', true],
            'other command' => ['/help', false],
            'regular message' => ['hello world', false],
            'empty message' => ['', false],
        ];
    }

    public function testExecute(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getBalance')->willReturn('100.00');

        $update = $this->createUpdate('/start');

        $this->translationService
            ->expects($this->once())
            ->method('getUserLocale')
            ->with('en')
            ->willReturn(\App\Entity\Locale::ENGLISH);

        $this->userManager
            ->expects($this->once())
            ->method('findOrCreateUser')
            ->with(123, 'testuser', 'John', 'Doe')
            ->willReturn($user);

        $this->translationService
            ->expects($this->exactly(4))
            ->method('translate')
            ->willReturnMap([
                ['bot.start.welcome', ['%balance%' => '100.00'], 'en', 'Welcome! Balance: 100.00 rub.'],
                ['bot.start.menu.free_trial', [], 'en', 'Free Trial'],
                ['bot.start.menu.my_configs', [], 'en', 'My Configs'],
                ['bot.start.menu.add_balance', [], 'en', 'Add Balance'],
            ]);

        $this->telegramBotApi
            ->expects($this->once())
            ->method('sendKeyboard')
            ->with(
                789,
                'Welcome! Balance: 100.00 rub.',
                [
                    [['text' => 'Free Trial', 'callback_data' => 'free_trial']],
                    [['text' => 'My Configs', 'callback_data' => 'my_configs']],
                    [['text' => 'Add Balance', 'callback_data' => 'add_balance']],
                ]
            );

        $this->command->execute($update);

        // Verify the command was executed successfully (no exceptions thrown)
        $this->assertTrue(true);
    }

    public function testExecuteHandlesInvalidData(): void
    {
        $update = new TelegramUpdateDTO(123);
        // Update without message should not cause errors in supports check
        $this->assertFalse($this->command->supports($update));
    }

    private function createUpdate(string $text): TelegramUpdateDTO
    {
        $user = new TelegramUserDTO(123, 'testuser', 'John', 'Doe', 'en');
        $message = new TelegramMessageDTO(
            456,
            789,
            $user,
            $text,
            new \DateTimeImmutable('@1640995200')
        );

        return new TelegramUpdateDTO(123, $message, null);
    }
}
