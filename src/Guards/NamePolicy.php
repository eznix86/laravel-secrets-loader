<?php

declare(strict_types=1);

namespace Eznix86\LaravelSecretsLoader\Guards;

use Eznix86\LaravelSecretsLoader\Contracts\NameGuard;
use Eznix86\LaravelSecretsLoader\Guards\Name\HasUnsafeCharacters;
use Eznix86\LaravelSecretsLoader\Guards\Name\IsRequestHeader;

final class NamePolicy implements NameGuard
{
    public function rejects(string $name): bool
    {
        foreach ($this->guards() as $guard) {
            if ($guard->rejects($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<NameGuard>
     */
    private function guards(): array
    {
        return [
            new IsRequestHeader,
            new HasUnsafeCharacters,
        ];
    }
}
