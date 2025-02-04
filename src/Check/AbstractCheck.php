<?php

namespace Sweikenb\Library\Boundaries\Check;

use Sweikenb\Library\Boundaries\Api\CheckInterface;

abstract class AbstractCheck implements CheckInterface
{
    public function addViolation(
        array &$violations,
        string $dir,
        string $filename,
        string $violation,
        ?string $key = null
    ): void {
        $key ??= $this->getConfigKey();
        $violations[sprintf("%s#%s#%s", $key, $dir, $filename)] = $violation;
    }

    public function removeViolation(array &$violations, string $dir, string $filename, ?string $key = null): void
    {
        $key ??= $this->getConfigKey();
        unset($violations[sprintf("%s#%s#%s", $key, $dir, $filename)]);
    }
}
