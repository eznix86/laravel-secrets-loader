<?php

declare(strict_types=1);

namespace Eznix86\LaravelSecretsLoader\Contracts;

interface NameGuard
{
    public function rejects(string $name): bool;
}
