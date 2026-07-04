<?php

declare(strict_types=1);

namespace App\UI\Console;

use App\Application\Repository\RiderWriteRepositoryInterface;
use App\Infrastructure\External\TourDeFrance\TourDeFrance2026PublishedStartListSource;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:tour-de-france-2026:import-published-startlist')]
final class ImportTourDeFrance2026PublishedStartListCommand extends Command
{
    public function __construct(
        private readonly TourDeFrance2026PublishedStartListSource $source,
        private readonly RiderWriteRepositoryInterface $riders,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $riders = $this->source->riders();
        $this->riders->replaceAllFromImport($riders);

        $output->writeln(sprintf('%d Tour de France 2026 riders imported from published startlist.', count($riders)));

        return Command::SUCCESS;
    }
}
