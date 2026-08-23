<?php

declare(strict_types=1);

namespace Eznix86\LaravelSecretsLoader\Sources;

use Eznix86\LaravelSecretsLoader\Environment;
use Eznix86\LaravelSecretsLoader\SecretFile;

final class DirectorySource implements SecretSource
{
    private const array VARIABLES = ['CREDENTIALS_DIRECTORY', 'NOMAD_SECRETS_DIR'];

    private const string DEFAULT_DIRECTORY = '/run/secrets';

    private const string SAFE_NAME = '/^[A-Za-z_][A-Za-z0-9_]*$/';

    public function locate(string $name): ?SecretFile
    {
        if (preg_match(self::SAFE_NAME, $name) !== 1) {
            return null;
        }

        foreach ($this->directories() as $directory) {
            foreach ([$name, strtolower($name)] as $filename) {
                $path = $directory.DIRECTORY_SEPARATOR.$filename;

                if (is_file($path)) {
                    return new SecretFile($path, $name);
                }
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function directories(): array
    {
        $directories = [];

        foreach (self::VARIABLES as $variable) {
            $directory = Environment::value($variable);

            if ($directory !== null && $directory !== '') {
                $directories[] = $directory;
            }
        }

        $directories[] = self::DEFAULT_DIRECTORY;

        return $directories;
    }
}
