<?php

declare(strict_types=1);

namespace Eznix86\LaravelSecretsLoader\Guards;

use Eznix86\LaravelSecretsLoader\SecretFile;

interface Guard
{
    public function check(SecretFile $file): void;
}
