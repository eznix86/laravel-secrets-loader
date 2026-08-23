<?php

declare(strict_types=1);

namespace Eznix86\LaravelSecretsLoader\Guards\Name;

use Eznix86\LaravelSecretsLoader\Contracts\NameGuard;

final class IsRequestHeader implements NameGuard
{
    private const string REDIRECT_PREFIX = 'REDIRECT_';

    private const string HEADER_PREFIX = 'HTTP_';

    public function rejects(string $name): bool
    {
        return str_starts_with($this->withoutRedirectPrefixes($name), self::HEADER_PREFIX);
    }

    private function withoutRedirectPrefixes(string $name): string
    {
        while (str_starts_with($name, self::REDIRECT_PREFIX)) {
            $name = substr($name, strlen(self::REDIRECT_PREFIX));
        }

        return $name;
    }
}
