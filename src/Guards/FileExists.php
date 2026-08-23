<?php

declare(strict_types=1);

namespace Eznix86\LaravelSecretsLoader\Guards;

use Eznix86\LaravelSecretsLoader\SecretFile;
use Eznix86\LaravelSecretsLoader\SecretFileException;

final class FileExists implements Guard
{
    public function check(SecretFile $file): void
    {
        if (! is_file($file->path())) {
            throw SecretFileException::missing($file->path(), $file->source());
        }
    }
}
