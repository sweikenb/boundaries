<?php

namespace Sweikenb\Library\Boundaries\Service;

use Symfony\Component\Yaml\Yaml;

class ConfigLoaderService
{
    public const CONFIG_FILENAME = 'boundaries.yaml';

    private function getConfigPath(): string
    {
        return getcwd().DIRECTORY_SEPARATOR.self::CONFIG_FILENAME;
    }

    private function hasConfigFile(): bool
    {
        $configPath = $this->getConfigPath();

        return file_exists($configPath) && is_readable($configPath);
    }

    public function load(): array
    {
        if ($this->hasConfigFile()) {
            return Yaml::parseFile($this->getConfigPath());
        }

        return [];
    }

    public function init(?string $template = null, bool $override = false): bool
    {
        if (!$override && $this->hasConfigFile()) {
            return false;
        }

        $template ??= $this->getDefaultTemplate();
        if (!in_array($template, $this->getTemplates())) {
            return false;
        }

        $templatePath = sprintf("%s/%s", $this->getTemplatesDir(), $template);
        if (file_exists($templatePath) && is_readable($templatePath)) {
            return file_put_contents($this->getConfigPath(), file_get_contents($templatePath)) > 0;
        }

        return false;
    }

    public function getTemplatesDir(): string
    {
        return realpath(sprintf("%s/../../templates", __DIR__));
    }

    public function getDefaultTemplate(): string
    {
        return self::CONFIG_FILENAME;
    }

    public function getTemplates(): array
    {
        return array_filter(
            scandir($this->getTemplatesDir()) ?: [],
            fn(string $path) => preg_match('/^[^.].+\.yaml$/', $path)
        );
    }
}
