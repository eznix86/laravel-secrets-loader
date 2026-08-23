<?php

declare(strict_types=1);

namespace Eznix86\LaravelSecretsLoader;

use Dotenv\Repository\Adapter\AdapterInterface;
use Eznix86\LaravelSecretsLoader\Contracts\SecretSource;
use Eznix86\LaravelSecretsLoader\Sources\DirectorySource;
use Eznix86\LaravelSecretsLoader\Sources\SuffixSource;
use PhpOption\None;
use PhpOption\Option;
use PhpOption\Some;

final class SecretFileAdapter implements AdapterInterface
{
    /**
     * @var array<string, Option<string>>
     */
    private array $resolved = [];

    /**
     * @return Option<AdapterInterface>
     */
    public static function create(): Option
    {
        return Some::create(self::newAdapter());
    }

    private static function newAdapter(): AdapterInterface
    {
        return new self;
    }

    /**
     * @param  non-empty-string  $name
     * @return Option<string>
     */
    public function read(string $name): Option
    {
        if (! isset($this->resolved[$name])) {
            $this->resolved[$name] = $this->resolve($name);
        }

        return $this->resolved[$name];
    }

    /**
     * @param  non-empty-string  $name
     */
    public function write(string $name, string $value): bool
    {
        return true;
    }

    /**
     * @param  non-empty-string  $name
     */
    public function delete(string $name): bool
    {
        return true;
    }

    /**
     * @return list<SecretSource>
     */
    private function sources(): array
    {
        return [
            new SuffixSource,
            new DirectorySource,
        ];
    }

    /**
     * @return Option<string>
     */
    private function resolve(string $name): Option
    {
        foreach ($this->sources() as $source) {
            $file = $source->locate($name);

            if ($file instanceof SecretFile) {
                return Some::create($file->contents());
            }
        }

        return None::create();
    }
}
