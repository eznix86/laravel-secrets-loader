<?php

declare(strict_types=1);

namespace Eznix86\LaravelSecretsLoader\Guards;

use Eznix86\LaravelSecretsLoader\Contracts\FileGuard;
use Eznix86\LaravelSecretsLoader\Guards\File\Exists;
use Eznix86\LaravelSecretsLoader\Guards\File\IsReadable;
use Eznix86\LaravelSecretsLoader\Guards\File\PathIsAbsolute;
use Eznix86\LaravelSecretsLoader\Guards\File\WithinSizeLimit;
use Eznix86\LaravelSecretsLoader\SecretFile;

final class FilePolicy implements FileGuard
{
    public function check(SecretFile $file): void
    {
        foreach ($this->guards() as $guard) {
            $guard->check($file);
        }
    }

    /**
     * @return list<FileGuard>
     */
    private function guards(): array
    {
        return [
            new PathIsAbsolute,
            new Exists,
            new IsReadable,
            new WithinSizeLimit,
        ];
    }
}
