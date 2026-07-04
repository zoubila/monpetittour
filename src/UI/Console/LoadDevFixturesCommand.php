<?php

declare(strict_types=1);

namespace App\UI\Console;

use App\Infrastructure\Fixture\RiderFixtureLoader;
use App\Infrastructure\Fixture\StageFixtureLoader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:fixtures:load-riders')]
final class LoadDevFixturesCommand extends Command
{
    public function __construct(
        private readonly RiderFixtureLoader $loader,
        private readonly StageFixtureLoader $stageLoader,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->loader->loadIfEmpty();
        $this->stageLoader->loadIfEmpty();
        $output->writeln('Rider fixtures loaded.');

        return Command::SUCCESS;
    }
}
