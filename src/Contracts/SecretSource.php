<?php

declare(strict_types=1);

namespace Eznix86\LaravelSecretsLoader\Contracts;

use Eznix86\LaravelSecretsLoader\SecretFile;

interface SecretSource
{
    public function locate(string $name): ?SecretFile;
}
