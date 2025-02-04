<?php

namespace Sweikenb\Library\Boundaries\Composer\Command;

use Composer\Command\BaseCommand;
use Sweikenb\Library\Boundaries\Service\CheckService;
use Sweikenb\Library\Boundaries\Service\ConfigLoaderService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckCommand extends BaseCommand
{
    private readonly CheckService $checkService;
    private readonly ConfigLoaderService $configLoaderService;

    public function __construct(
        ?CheckService $checkService = null,
        ?ConfigLoaderService $configLoaderService = null,
        ?string $name = null
    ) {
        $this->checkService = $checkService ?? new CheckService();
        $this->configLoaderService = $configLoaderService ?? new ConfigLoaderService();
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('boundaries:check');
        $this->setDescription(
            sprintf("Performs the checks configured in your \"%s\"-file", ConfigLoaderService::CONFIG_FILENAME)
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $result = $this->checkService->execute($this->configLoaderService->load());
        if ($result->numViolations() === 0) {
            $io->success('Boundaries: no violations found');

            return self::SUCCESS;
        }

        foreach ($result->violations as $violation) {
            $io->writeln(sprintf("<error>Boundaries: %s</error>", $violation));
        }

        return self::FAILURE;
    }
}
