<?php

declare(strict_types=1);

use Eznix86\LaravelSecretsLoader\Guards\File\PathIsAbsolute;
use Eznix86\LaravelSecretsLoader\SecretFile;
use Eznix86\LaravelSecretsLoader\SecretFileException;

function guardFor(string $path): Closure
{
    return fn () => (new PathIsAbsolute)->check(new SecretFile($path, 'DB_PASSWORD_FILE'));
}

it('accepts a unix path', function (): void {
    expect(guardFor('/run/secrets/db'))->not->toThrow(SecretFileException::class);
});

it('accepts a windows drive path with backslashes', function (): void {
    expect(guardFor('C:\\secrets\\db'))->not->toThrow(SecretFileException::class);
});

it('accepts a windows drive path with forward slashes', function (): void {
    expect(guardFor('C:/secrets/db'))->not->toThrow(SecretFileException::class);
});

it('accepts a lowercase drive letter', function (): void {
    expect(guardFor('d:/secrets/db'))->not->toThrow(SecretFileException::class);
});

it('accepts a unc path', function (): void {
    expect(guardFor('\\\\server\\share\\db'))->not->toThrow(SecretFileException::class);
});

it('rejects a bare relative path', function (): void {
    expect(guardFor('secrets/db'))->toThrow(SecretFileException::class, 'must be absolute');
});

it('rejects a dot relative path', function (): void {
    expect(guardFor('./secrets/db'))->toThrow(SecretFileException::class, 'must be absolute');
});

it('rejects a parent relative path', function (): void {
    expect(guardFor('../secrets/db'))->toThrow(SecretFileException::class, 'must be absolute');
});

it('rejects a drive letter with no separator', function (): void {
    expect(guardFor('C:secrets'))->toThrow(SecretFileException::class, 'must be absolute');
});

it('rejects an empty path', function (): void {
    expect(guardFor(''))->toThrow(SecretFileException::class, 'must be absolute');
});
