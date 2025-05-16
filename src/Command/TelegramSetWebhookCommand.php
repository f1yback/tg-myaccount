<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Telegram\TelegramBotApiInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'telegram:set-webhook',
    description: 'Set Telegram bot webhook URL',
)]
class TelegramSetWebhookCommand extends Command
{
    private TelegramBotApiInterface $telegramBotService;

    public function __construct(TelegramBotApiInterface $telegramBotService)
    {
        $this->telegramBotService = $telegramBotService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('url', InputArgument::REQUIRED, 'Webhook URL')
            ->addOption('delete', null, InputOption::VALUE_NONE, 'Delete webhook instead of setting it');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $url = $input->getArgument('url');
        $delete = $input->getOption('delete');

        try {
            if ($delete) {
                $success = $this->telegramBotService->deleteWebhook();
                $message = $success ? 'Webhook deleted successfully' : 'Failed to delete webhook';
            } else {
                $success = $this->telegramBotService->setWebhook($url);
                $message = $success ? 'Webhook set successfully to: '.$url : 'Failed to set webhook';
            }

            if ($success) {
                $io->success($message);

                return Command::SUCCESS;
            } else {
                $io->error($message);

                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $io->error('Error: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
