<?php

declare(strict_types=1);

namespace Eznix86\LaravelSecretsLoader;

use Dotenv\Repository\Adapter\AdapterInterface;
use Eznix86\LaravelSecretsLoader\Sources\DirectorySource;
use Eznix86\LaravelSecretsLoader\Sources\SecretSource;
use Eznix86\LaravelSecretsLoader\Sources\SuffixSource;
use PhpOption\None;
use PhpOption\Option;
use PhpOption\Some;

final class SecretFileAdapter implements AdapterInterface
{
    /**
     * @var list<SecretSource>
     */
    private readonly array $sources;

    /**
     * @var array<string, Option<string>>
     */
    private array $resolved = [];

    public function __construct()
    {
        $this->sources = [new SuffixSource, new DirectorySource];
    }

    /**
     * @return Option<AdapterInterface>
     */
    public static function create(): Option
    {
        return Some::create(self::adapter());
    }

    /**
     * @param  non-empty-string  $name
     * @return Option<string>
     */
    public function read(string $name): Option
    {
        return $this->resolved[$name] ??= $this->resolve($name);
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

    private static function adapter(): AdapterInterface
    {
        return new self;
    }

    /**
     * @return Option<string>
     */
    private function resolve(string $name): Option
    {
        foreach ($this->sources as $source) {
            $file = $source->locate($name);

            if ($file instanceof SecretFile) {
                return Some::create($file->contents());
            }
        }

        return None::create();
    }
}
