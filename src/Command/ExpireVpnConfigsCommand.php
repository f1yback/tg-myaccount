<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Vpn\VpnConfigManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'vpn:expire-configs',
    description: 'Expire old VPN configurations',
)]
class ExpireVpnConfigsCommand extends Command
{
    public function __construct(
        private readonly VpnConfigManagerInterface $vpnConfigManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Starting to expire old VPN configurations...');

        try {
            $expiredCount = $this->vpnConfigManager->expireOldConfigs();

            if ($expiredCount > 0) {
                $io->success("Expired {$expiredCount} VPN configuration(s)");
            } else {
                $io->info('No configurations to expire');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Error expiring configurations: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
