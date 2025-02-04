<?php

namespace Sweikenb\Library\Boundaries\Api;

interface CheckInterface
{
    public const PRIO_VERY_EARLY = 1;
    public const PRIO_EARLY = 50;
    public const PRIO_DEFAULT = 100;
    public const PRIO_LATE = 150;
    public const PRIO_VERY_LATE = 999;

    public static function getConfigKey(): string;

    public static function getPriority(): int;

    public function execute(
        array $checkConfig,
        string $dir,
        string $filename,
        string &$content,
        array &$violations
    ): void;
}
