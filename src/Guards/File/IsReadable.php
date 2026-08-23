<?php

declare(strict_types=1);

namespace Eznix86\LaravelSecretsLoader\Guards\File;

use Eznix86\LaravelSecretsLoader\Contracts\FileGuard;
use Eznix86\LaravelSecretsLoader\SecretFile;
use Eznix86\LaravelSecretsLoader\SecretFileException;

final class IsReadable implements FileGuard
{
    public function check(SecretFile $file): void
    {
        if (! is_readable($file->path())) {
            throw SecretFileException::unreadable($file->path(), $file->source());
        }
    }
}
