<?php

namespace Sweikenb\Library\Boundaries\Service;

use Sweikenb\Library\Boundaries\Api\CheckInterface;
use Sweikenb\Library\Boundaries\Check\ContentAllowCheck;
use Sweikenb\Library\Boundaries\Check\ContentDenyCheck;
use Sweikenb\Library\Boundaries\Check\FilenameCheck;
use Sweikenb\Library\Boundaries\Factory\CheckResultFactory;
use Sweikenb\Library\Boundaries\Model\CheckResultModel;
use Symfony\Component\Finder\Finder;

class CheckService
{
    /**
     * @var CheckInterface[]
     */
    private static array $checks = [];
    private readonly CheckResultFactory $resultFactory;

    public function __construct(?CheckResultFactory $resultFactory = null)
    {
        $this->resultFactory = $resultFactory ?? new CheckResultFactory();
        self::registerChecks(
            new ContentAllowCheck(),
            new ContentDenyCheck(),
            new FilenameCheck()
        );
    }

    public static function registerChecks(CheckInterface...$checks): void
    {
        foreach ($checks as $check) {
            if (!isset(self::$checks[$check::getPriority()])) {
                self::$checks[$check::getPriority()] = [];
            }
            self::$checks[$check::getPriority()][] = $check;
        }
    }

    /**
     * @return array<string, array<int, CheckInterface>>
     */
    public function getChecksByPriorityAndKey(): array
    {
        ksort(self::$checks);

        $prioritized = [];
        foreach (self::$checks as $checks) {
            foreach ($checks as $check) {
                /* @var CheckInterface $check */
                if (!isset($prioritized[$check::getConfigKey()])) {
                    $prioritized[$check::getConfigKey()] = [];
                }
                $prioritized[$check::getConfigKey()][] = $check;
            }
        }

        return $prioritized;
    }

    public function getFinder(string $dir): Finder
    {
        $finder = new Finder();
        $finder->files()->in($dir);

        return $finder;
    }

    public function execute(array $boundariesConfig): CheckResultModel
    {
        if (!isset($boundariesConfig['version'])) {
            return $this->resultFactory->create(
                'Please specify the version inside your boundaries configuration file!'
            );
        }
        if (intval($boundariesConfig['version']) !== 1) {
            return $this->resultFactory->create(
                sprintf('Unsupported boundaries configuration file version: "%d"', $boundariesConfig['version'])
            );
        }

        $contentLoadPatterns = (array)($boundariesConfig['content']['only-for'] ?? []);

        $paths = $boundariesConfig['paths'] ?? [];
        $checks = $this->getChecksByPriorityAndKey();

        $violations = [];
        foreach ($paths as $dir => $config) {
            $applyConfig = $config['checks'] ?? [];
            if (empty($applyConfig) || !is_dir($dir)) {
                continue;
            }

            $finder = $this->getFinder($dir);
            if (!$finder->hasResults()) {
                continue;
            }

            foreach ($finder as $file) {
                $filename = $file->getRelativePathname();

                $content = '';
                foreach ($contentLoadPatterns as $contentLoadPattern) {
                    if (!empty($contentLoadPattern) && preg_match($contentLoadPattern, $filename)) {
                        $content = $file->getContents();
                        break;
                    }
                }

                foreach ($applyConfig as $checkKey => $checkConfig) {
                    foreach ($checks[$checkKey] ?? [] as $check) {
                        $check->execute($checkConfig, $dir, $filename, $content, $violations);
                    }
                }
                unset($content);
            }
        }

        $violations = array_unique(array_values($violations));
        sort($violations);

        return $this->resultFactory->create(...$violations);
    }
}
