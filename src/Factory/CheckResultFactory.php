<?php

namespace Sweikenb\Library\Boundaries\Factory;

use Sweikenb\Library\Boundaries\Model\CheckResultModel;

class CheckResultFactory
{
    public function create(string ...$violations): CheckResultModel
    {
        return new CheckResultModel($violations);
    }
}
