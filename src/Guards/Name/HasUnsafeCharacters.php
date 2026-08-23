<?php

declare(strict_types=1);

namespace Eznix86\LaravelSecretsLoader\Guards\Name;

use Eznix86\LaravelSecretsLoader\Contracts\NameGuard;

final class HasUnsafeCharacters implements NameGuard
{
    private const string ENVIRONMENT_VARIABLE = '/^[A-Za-z_][A-Za-z0-9_]*$/';

    public function rejects(string $name): bool
    {
        return ! $this->looksLikeAnEnvironmentVariable($name);
    }

    private function looksLikeAnEnvironmentVariable(string $name): bool
    {
        return preg_match(self::ENVIRONMENT_VARIABLE, $name) === 1;
    }
}
