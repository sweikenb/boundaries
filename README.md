# Boundaries

This is a plugin for the [Composer](https://getcomposer.org) package-manager.

License: **MIT**

Project status: **functional but WIP**

## Installation

```bash
# install composer plugin
composer require sweikenb/boundaries

# init the project configuration file
composer boundaries:init
```

**Options:**

```bash
# use a pre-configured symfony-template as staring point for your configuration
composer boundaries:init --template=symfony.yaml

# force override an existing configuration
composer boundaries:init --force
```

## Run the check

You can run this command locally or in your CI/CD pipelines, based on the exit-status of the script you can identify if
violations where found:

```bash
# User-friendly output
composer boundaries:check

# Omit output
composer boundaries:check -q
```

If any violation is found, the corresponding errors will be printed and **the script will exit with a non-zero status**.

In case of no violations, the script will print a success message and exit with a zero status.

## Configuration

Boundaries will look for its configuration file `boundaries.yaml` in the composer working directory of the project.

Please refer to the template-config for further descriptions: [boundaries.yaml](templates/boundaries.yaml)

## Add custom checks

In order to add custom checks, you have to create a [composer plugin](https://getcomposer.org/doc/articles/plugins.md)
and register custom checks in the plugin:

### Create your check

Create your own check by implementing `\Sweikenb\Library\Boundaries\Api\CheckInterface` directly or extending the
abstract check (which is recommended):

```yaml
version: 1
paths:
  src/Some/Directory:
    label: "My example directory"
    checks:
      # ...

      # Add custom check configuration that will trigger a violation in this case:
      needles:
        filename: "#(some needle)#i"
        content: "#(another needle)#i"
```

```php
class MyCustomCheckForNeedles extends AbstractCheck
{
    public static function getConfigKey(): string
    {
        // This is the checks-key in the "boundaries.yaml"-file that will be active the check and contains specific
        // configurations.
        return 'needles';
    }

    public static function getPriority(): int
    {
        // In order to influence the execution order of checks, you can specify a priority here,
        // the lower the number the earlier the execution.
        return self::PRIO_DEFAULT;
    }

    public function execute(
        array $checkConfig,
        string $dir,
        string $filename,
        string &$content,
        array &$violations
    ): void {
        // Please note that the $violations and $content variables must be passed as reference!
        // While adding error-messages to the $violations is the intended way, the $content is passed by reference
        // to maintain viable performance and prevent memory issues.
        //
        // IMPORTANT: Changes to $content WILL affect other checks and should only be done intentionally!
        //
        if (isset($checkConfig['filename']) && preg_match($checkConfig['filename'], $filename)) {
            $this->addViolation($violations, $dir, $filename, sprintf('A needle was found in filename "%s"', $filename));
            break;
        }
        if (isset($checkConfig['content']) && preg_match($checkConfig['content'], $content)) {
            $this->addViolation($violations, $dir, $filename, sprintf('A needle was found in file-content "%s"', $filename));
            break;
        }
    }
}
```

### Register your checks

You have to call the `\Sweikenb\Library\Boundaries\Service\CheckService::registerChecks` static method to register you
checks in your plugin activation hook:

```php
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Sweikenb\Library\Boundaries\Service\CheckService;

class MyCustomBoundariesChecksPlugin implements PluginInterface
{
    public function activate(Composer $composer, IOInterface $io): void
    {
        CheckService::registerChecks(
            new MyCustomCheckForNeedles()
        );
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
        // nothing to do here
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
        // nothing to do here
    }
}
```

### Troubleshoot

**I want to add a custom check that validates the content of a file but `$content` is always empty**

You likely want to check contents of a file which name/filetype is not whitelisted for content loading.

Simply add your filename/-extension to the corresponding configuration of your `boundaries.yaml`:

```yaml
# ...

content:
  only-for: "#\\.(php|twig|json|yaml|yml|xml|my-custom-filetype-to-load)$#i"

# ...
```
