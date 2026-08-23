<?php

declare(strict_types=1);

namespace Eznix86\LaravelSecretsLoader\Sources;

use Eznix86\LaravelSecretsLoader\Contracts\SecretSource;
use Eznix86\LaravelSecretsLoader\Environment;
use Eznix86\LaravelSecretsLoader\Guards\NamePolicy;
use Eznix86\LaravelSecretsLoader\SecretFile;

final class DirectorySource implements SecretSource
{
    private const array VARIABLES = ['CREDENTIALS_DIRECTORY', 'NOMAD_SECRETS_DIR'];

    private const string DEFAULT_DIRECTORY = '/run/secrets';

    public function locate(string $name): ?SecretFile
    {
        if ((new NamePolicy)->rejects($name)) {
            return null;
        }

        foreach ($this->candidatePaths($name) as $path) {
            if (is_file($path)) {
                return new SecretFile($path, $name);
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function candidatePaths(string $name): array
    {
        $paths = [];

        foreach ($this->directories() as $directory) {
            foreach ($this->filenamesFor($name) as $filename) {
                $paths[] = $directory.DIRECTORY_SEPARATOR.$filename;
            }
        }

        return $paths;
    }

    /**
     * @return list<string>
     */
    private function filenamesFor(string $name): array
    {
        return [$name, strtolower($name)];
    }

    /**
     * @return list<string>
     */
    private function directories(): array
    {
        $directories = [];

        foreach (self::VARIABLES as $variable) {
            $directory = Environment::value($variable);

            if ($directory !== null) {
                $directories[] = $directory;
            }
        }

        $directories[] = self::DEFAULT_DIRECTORY;

        return $directories;
    }
}
