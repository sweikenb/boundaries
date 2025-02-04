<?php

namespace Sweikenb\Library\Boundaries\Check;

class ContentAllowCheck extends AbstractCheck
{
    public static function getConfigKey(): string
    {
        return 'content-allow';
    }

    public static function getPriority(): int
    {
        return self::PRIO_LATE;
    }

    public function execute(
        array $checkConfig,
        string $dir,
        string $filename,
        string &$content,
        array &$violations
    ): void {
        foreach ($checkConfig as $pattern) {
            if (preg_match($pattern, $content)) {
                $this->removeViolation($violations, $dir, $filename, ContentDenyCheck::getConfigKey());
                break;
            }
        }
    }
}
