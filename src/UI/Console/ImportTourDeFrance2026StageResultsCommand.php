<?php

declare(strict_types=1);

namespace App\UI\Console;

use App\Application\Handler\ImportTourDeFrance2026StageResultsHandler;
use App\Infrastructure\External\Letour\LetourScrapingUnavailable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:tour-de-france-2026:import-stage-results')]
final class ImportTourDeFrance2026StageResultsCommand extends Command
{
    public function __construct(private readonly ImportTourDeFrance2026StageResultsHandler $importStageResults)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('stageNumber', InputArgument::REQUIRED, 'Tour de France stage number.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stageNumber = (int) $input->getArgument('stageNumber');

        if ($stageNumber < 1 || $stageNumber > 21) {
            $output->writeln('<error>stageNumber must be between 1 and 21.</error>');

            return Command::INVALID;
        }

        try {
            $report = ($this->importStageResults)($stageNumber);
        } catch (LetourScrapingUnavailable $exception) {
            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));

            return Command::FAILURE;
        }

        if ($report->unmatchedRiderNames !== []) {
            $output->writeln('<error>Some riders from Letour could not be matched with local riders.</error>');
            foreach ($report->unmatchedRiderNames as $riderName) {
                $output->writeln(sprintf('- %s', $riderName));
            }

            return Command::FAILURE;
        }

        if ($report->importedResultCount === 0) {
            $output->writeln(sprintf('<comment>No results found for stage %d.</comment>', $stageNumber));

            return Command::FAILURE;
        }

        $output->writeln(sprintf(
            '%d rider results imported for Tour de France 2026 stage %d.',
            $report->importedResultCount,
            $stageNumber,
        ));

        return Command::SUCCESS;
    }
}
