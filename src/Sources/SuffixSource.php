<?php

declare(strict_types=1);

namespace Eznix86\LaravelSecretsLoader\Sources;

use Eznix86\LaravelSecretsLoader\Contracts\SecretSource;
use Eznix86\LaravelSecretsLoader\Environment;
use Eznix86\LaravelSecretsLoader\SecretFile;

final class SuffixSource implements SecretSource
{
    private const array SUFFIXES = ['_FILE', '_PATH'];

    public function locate(string $name): ?SecretFile
    {
        if ($this->alreadyNamesAPath($name)) {
            return null;
        }

        foreach (self::SUFFIXES as $suffix) {
            $variable = $name.$suffix;
            $path = Environment::value($variable);

            if ($path !== null) {
                return new SecretFile($path, $variable);
            }
        }

        return null;
    }

    private function alreadyNamesAPath(string $name): bool
    {
        foreach (self::SUFFIXES as $suffix) {
            if (str_ends_with($name, $suffix)) {
                return true;
            }
        }

        return false;
    }
}
