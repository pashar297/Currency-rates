<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\ExchangeRateUpdaterService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(name: 'app:update-exchange-rates')]
final class UpdateExchangeRatesCommand extends Command
{
    public function __construct(
        private readonly ExchangeRateUpdaterService $exchangeRateUpdaterService,
        private readonly LoggerInterface $exchangeRatesLogger
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $results         = $this->exchangeRateUpdaterService->updateRates();
            $totalUpdated    = 0;
            $failedProviders = [];

            foreach ($results as $provider => $result) {
                if ($result['success']) {
                    $totalUpdated += $result['rates_saved'];
                } else {
                    $failedProviders[] = $provider;
                }
            }

            $this->exchangeRatesLogger->info('Exchange rates update completed', [
                'total_updated'    => $totalUpdated,
                'failed_providers' => $failedProviders,
            ]);

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->exchangeRatesLogger->error('Exchange rates update failed', ['exception' => $e]);
            return Command::FAILURE;
        }
    }
}
