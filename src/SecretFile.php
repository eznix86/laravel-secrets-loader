<?php

declare(strict_types=1);

namespace Eznix86\LaravelSecretsLoader;

use Eznix86\LaravelSecretsLoader\Guards\FileExists;
use Eznix86\LaravelSecretsLoader\Guards\FileIsReadable;
use Eznix86\LaravelSecretsLoader\Guards\FileWithinSizeLimit;
use Eznix86\LaravelSecretsLoader\Guards\Guard;
use Eznix86\LaravelSecretsLoader\Guards\PathIsAbsolute;

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
        foreach ($this->guards() as $guard) {
            $guard->check($this);
        }

        $contents = file_get_contents($this->path);

        if ($contents === false) {
            throw SecretFileException::unreadable($this->path, $this->source);
        }

        return rtrim($contents, "\r\n");
    }

    /**
     * @return list<Guard>
     */
    private function guards(): array
    {
        return [
            new PathIsAbsolute,
            new FileExists,
            new FileIsReadable,
            new FileWithinSizeLimit,
        ];
    }
}
