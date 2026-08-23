<?php

declare(strict_types=1);

namespace Eznix86\LaravelSecretsLoader\Guards\File;

use Eznix86\LaravelSecretsLoader\Contracts\FileGuard;
use Eznix86\LaravelSecretsLoader\SecretFile;
use Eznix86\LaravelSecretsLoader\SecretFileException;

final class WithinSizeLimit implements FileGuard
{
    private const int MAX_BYTES = 1024 * 1024; // 1 MiB

    public function check(SecretFile $file): void
    {
        $size = filesize($file->path());

        if ($size === false) {
            throw SecretFileException::unreadable($file->path(), $file->source());
        }

        if ($size > self::MAX_BYTES) {
            throw SecretFileException::tooLarge($file->path(), $file->source(), self::MAX_BYTES);
        }
    }
}
