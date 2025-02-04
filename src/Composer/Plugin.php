<?php

namespace Sweikenb\Library\Boundaries\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;

class Plugin implements PluginInterface, Capable
{
    public function getCapabilities(): array
    {
        return [
            'Composer\Plugin\Capability\CommandProvider' => 'Sweikenb\Library\Boundaries\Composer\Provider\CommandProvider',
        ];
    }

    public function activate(Composer $composer, IOInterface $io): void
    {
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }
}
