<?php

declare(strict_types=1);

namespace Eznix86\LaravelSecretsLoader\Guards;

use Eznix86\LaravelSecretsLoader\SecretFile;
use Eznix86\LaravelSecretsLoader\SecretFileException;

final class FileIsReadable implements Guard
{
    public function check(SecretFile $file): void
    {
        if (! is_readable($file->path())) {
            throw SecretFileException::unreadable($file->path(), $file->source());
        }
    }
}
