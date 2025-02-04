<?php

namespace Sweikenb\Library\Boundaries\Model;

class CheckResultModel
{
    /**
     * @param string[] $violations
     */
    public function __construct(
        public readonly array $violations
    ) {
    }

    public function numViolations(): int
    {
        return count($this->violations);
    }
}
