<?php

namespace Sweikenb\Library\Boundaries\Composer\Command;

use Composer\Command\BaseCommand;
use Sweikenb\Library\Boundaries\Service\ConfigLoaderService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InitCommand extends BaseCommand
{
    private ConfigLoaderService $configLoaderService;

    public function __construct(
        ?ConfigLoaderService $configLoaderService = null,
        ?string $name = null
    ) {
        $this->configLoaderService = $configLoaderService ?? new ConfigLoaderService();
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $defaultTemplate = $this->configLoaderService->getDefaultTemplate();
        $knownTemplates = $this->configLoaderService->getTemplates();

        $this->setName('boundaries:init');
        $this->setDescription(
            sprintf("Initializes the \"%s\"-file (only if it is not present yet)", ConfigLoaderService::CONFIG_FILENAME)
        );
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Override the existing config-file (if present)');
        $this->addOption(
            'template',
            null,
            InputOption::VALUE_REQUIRED,
            sprintf(
                'Template to use (default: <info>%s</info>): <comment>%s</comment>',
                $defaultTemplate,
                implode('</comment>, <comment>', $knownTemplates)
            )
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $override = (bool)$input->getOption('force');
        $template = $input->getOption('template');
        if (empty($template)) {
            $template = $this->configLoaderService->getDefaultTemplate();
        }
        if (!in_array($template, $this->configLoaderService->getTemplates())) {
            $io->error(sprintf('Unknown template "%s"', $template));

            return self::FAILURE;
        }

        if ($this->configLoaderService->init($template, $override)) {
            $io->success('Boundaries configuration initialized successfully');

            return self::SUCCESS;
        }

        $io->error('Boundaries configuration initialization failed');

        return self::FAILURE;
    }
}
