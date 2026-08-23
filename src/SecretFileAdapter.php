<?php

declare(strict_types=1);

namespace Eznix86\LaravelSecretsLoader;

use Dotenv\Repository\Adapter\AdapterInterface;
use PhpOption\None;
use PhpOption\Option;
use PhpOption\Some;

final class SecretFileAdapter implements AdapterInterface
{
    private const array SUFFIXES = ['_FILE', '_PATH'];

    private const array DIRECTORY_VARIABLES = ['CREDENTIALS_DIRECTORY', 'NOMAD_SECRETS_DIR'];

    private const string DEFAULT_DIRECTORY = '/run/secrets';

    private const string SAFE_NAME = '/^[A-Za-z_][A-Za-z0-9_]*$/';

    /**
     * @var array<string, Option<string>>
     */
    private array $resolved = [];

    /**
     * @return Option<AdapterInterface>
     */
    public static function create(): Option
    {
        return Some::create(self::adapter());
    }

    private static function adapter(): AdapterInterface
    {
        return new self;
    }

    /**
     * @param  non-empty-string  $name
     * @return Option<string>
     */
    public function read(string $name): Option
    {
        return $this->resolved[$name] ??= $this->resolve($name);
    }

    /**
     * @param  non-empty-string  $name
     */
    public function write(string $name, string $value): bool
    {
        return true;
    }

    /**
     * @param  non-empty-string  $name
     */
    public function delete(string $name): bool
    {
        return true;
    }

    /**
     * @return Option<string>
     */
    private function resolve(string $name): Option
    {
        foreach (self::SUFFIXES as $suffix) {
            if (str_ends_with($name, $suffix)) {
                return None::create();
            }
        }

        foreach (self::SUFFIXES as $suffix) {
            $path = $this->environmentValue($name.$suffix);

            if ($path !== null && $path !== '') {
                return Some::create($this->contents($path, $name.$suffix));
            }
        }

        if (preg_match(self::SAFE_NAME, $name) !== 1) {
            return None::create();
        }

        foreach ($this->directories() as $directory) {
            foreach ([$name, strtolower($name)] as $filename) {
                $path = $directory.DIRECTORY_SEPARATOR.$filename;

                if (is_file($path)) {
                    return Some::create($this->contents($path, $name));
                }
            }
        }

        return None::create();
    }

    /**
     * @return list<string>
     */
    private function directories(): array
    {
        $directories = [];

        foreach (self::DIRECTORY_VARIABLES as $variable) {
            $directory = $this->environmentValue($variable);

            if ($directory !== null && $directory !== '') {
                $directories[] = $directory;
            }
        }

        $directories[] = self::DEFAULT_DIRECTORY;

        return $directories;
    }

    private function contents(string $path, string $source): string
    {
        if (! is_file($path)) {
            throw SecretFileException::missing($path, $source);
        }

        if (! is_readable($path)) {
            throw SecretFileException::unreadable($path, $source);
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw SecretFileException::unreadable($path, $source);
        }

        return rtrim($contents, "\r\n");
    }

    private function environmentValue(string $name): ?string
    {
        foreach ([$_ENV, $_SERVER] as $source) {
            if (isset($source[$name]) && is_string($source[$name])) {
                return $source[$name];
            }
        }

        $value = getenv($name);

        return $value === false ? null : $value;
    }
}
