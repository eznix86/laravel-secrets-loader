<?php

declare(strict_types=1);

namespace Eznix86\LaravelSecretsLoader;

use RuntimeException;

final class SecretFileException extends RuntimeException
{
    public static function missing(string $path, string $source): self
    {
        return new self("Secret file [{$path}] referenced by [{$source}] does not exist.");
    }

    public static function unreadable(string $path, string $source): self
    {
        return new self("Secret file [{$path}] referenced by [{$source}] is not readable by the current user.");
    }
}
