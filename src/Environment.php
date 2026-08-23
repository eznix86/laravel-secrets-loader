<?php

declare(strict_types=1);

namespace Eznix86\LaravelSecretsLoader;

final class Environment
{
    private const string REQUEST_DATA = '/^(?:REDIRECT_)*HTTP_/';

    public static function value(string $name): ?string
    {
        if (preg_match(self::REQUEST_DATA, $name) === 1) {
            return null;
        }

        foreach ([$_ENV, $_SERVER] as $source) {
            if (isset($source[$name]) && is_string($source[$name])) {
                return $source[$name];
            }
        }

        $value = getenv($name);

        return $value === false ? null : $value;
    }
}
