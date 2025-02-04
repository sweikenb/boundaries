<?php

namespace Sweikenb\Library\Boundaries\Composer\Provider;

use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use Sweikenb\Library\Boundaries\Composer\Command\CheckCommand;
use Sweikenb\Library\Boundaries\Composer\Command\InitCommand;

class CommandProvider implements CommandProviderCapability
{
    public function getCommands(): array
    {
        return [
            new CheckCommand(),
            new InitCommand(),
        ];
    }
}
