<?php

namespace Sweikenb\Library\Boundaries\Check;

class ContentDenyCheck extends AbstractCheck
{
    public static function getConfigKey(): string
    {
        return 'content-deny';
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
        foreach ($checkConfig as $pattern) {
            if (preg_match($pattern, $content)) {
                $this->addViolation(
                    $violations,
                    $dir,
                    $filename,
                    sprintf(
                        'File "%s" does not match content policies! Found match for: %s',
                        $filename,
                        $pattern
                    )
                );
                break;
            }
        }
    }
}
