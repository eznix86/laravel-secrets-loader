<?php

declare(strict_types=1);

namespace Eznix86\LaravelSecretsLoader;

use Eznix86\LaravelSecretsLoader\Guards\NamePolicy;

final class Environment
{
    public static function value(string $name): ?string
    {
        if ((new NamePolicy)->rejects($name)) {
            return null;
        }

        foreach ([$_ENV, $_SERVER] as $source) {
            if (isset($source[$name]) && is_string($source[$name]) && $source[$name] !== '') {
                return $source[$name];
            }
        }

        $value = getenv($name);

        if ($value === false || $value === '') {
            return null;
        }

        return $value;
    }
}
