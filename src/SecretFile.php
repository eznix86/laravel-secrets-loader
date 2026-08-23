<?php

declare(strict_types=1);

namespace Eznix86\LaravelSecretsLoader;

use Eznix86\LaravelSecretsLoader\Guards\FilePolicy;

final readonly class SecretFile
{
    public function __construct(
        private string $path,
        private string $source,
    ) {}

    public function path(): string
    {
        return $this->path;
    }

    public function source(): string
    {
        return $this->source;
    }

    public function contents(): string
    {
        (new FilePolicy)->check($this);

        $contents = file_get_contents($this->path);

        if ($contents === false) {
            throw SecretFileException::unreadable($this->path, $this->source);
        }

        return rtrim($contents, "\r\n");
    }
}
