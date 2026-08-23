<?php

declare(strict_types=1);

namespace Eznix86\LaravelSecretsLoader\Guards;

use Eznix86\LaravelSecretsLoader\SecretFile;
use Eznix86\LaravelSecretsLoader\SecretFileException;

final class PathIsAbsolute implements Guard
{
    public function check(SecretFile $file): void
    {
        if (! $this->isAbsolute($file->path())) {
            throw SecretFileException::relative($file->path(), $file->source());
        }
    }

    private function isAbsolute(string $path): bool
    {
        return $this->isRooted($path) || $this->isWindowsDrive($path);
    }

    private function isRooted(string $path): bool
    {
        return isset($path[0]) && ($path[0] === '/' || $path[0] === '\\');
    }

    private function isWindowsDrive(string $path): bool
    {
        return strlen($path) >= 3
            && ctype_alpha($path[0])
            && $path[1] === ':'
            && ($path[2] === '/' || $path[2] === '\\');
    }
}
