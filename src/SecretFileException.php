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

    public static function relative(string $path, string $source): self
    {
        return new self("Secret file path [{$path}] referenced by [{$source}] must be absolute.");
    }

    public static function tooLarge(string $path, string $source, int $limit): self
    {
        return new self("Secret file [{$path}] referenced by [{$source}] is larger than {$limit} bytes.");
    }

    public static function unreadable(string $path, string $source): self
    {
        return new self("Secret file [{$path}] referenced by [{$source}] is not readable by the current user.");
    }
}
