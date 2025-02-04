<?php

namespace Sweikenb\Library\Boundaries\Check;

class FilenameCheck extends AbstractCheck
{
    public static function getConfigKey(): string
    {
        return 'files';
    }

    public static function getPriority(): int
    {
        return self::PRIO_DEFAULT;
    }

    public function execute(
        array $checkConfig,
        string $dir,
        string $filename,
        string &$content,
        array &$violations
    ): void {
        $fileOk = false;
        foreach ($checkConfig as $pattern) {
            if (preg_match($pattern, $filename)) {
                $fileOk = true;
                break;
            }
        }
        if (!$fileOk) {
            $this->addViolation(
                $violations,
                $dir,
                $filename,
                sprintf('The file "%s/%s" does not match the naming conventions.', $dir, $filename)
            );
        }
    }
}
