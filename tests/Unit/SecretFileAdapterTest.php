<?php

declare(strict_types=1);

use Eznix86\LaravelSecretsLoader\SecretFileAdapter;
use Eznix86\LaravelSecretsLoader\SecretFileException;
use PhpOption\Option;

beforeEach(function (): void {
    $this->directory = sys_get_temp_dir().'/secrets-'.bin2hex(random_bytes(6));

    mkdir($this->directory, 0700, true);
});

afterEach(function (): void {
    foreach (glob($this->directory.'/*') ?: [] as $file) {
        unlink($file);
    }

    rmdir($this->directory);

    unset(
        $_SERVER['DB_PASSWORD_FILE'],
        $_SERVER['DB_PASSWORD_PATH'],
        $_SERVER['CREDENTIALS_DIRECTORY'],
        $_SERVER['NOMAD_SECRETS_DIR'],
    );
});

function secret(string $directory, string $name, string $contents): string
{
    $path = $directory.'/'.$name;

    file_put_contents($path, $contents);

    return $path;
}

it('reads the file named by the _FILE suffix', function (): void {
    $_SERVER['DB_PASSWORD_FILE'] = secret($this->directory, 'db', "s3cret\n");

    expect((new SecretFileAdapter)->read('DB_PASSWORD')->get())->toBe('s3cret');
});

it('reads the file named by the _PATH suffix', function (): void {
    $_SERVER['DB_PASSWORD_PATH'] = secret($this->directory, 'db', 's3cret');

    expect((new SecretFileAdapter)->read('DB_PASSWORD')->get())->toBe('s3cret');
});

it('prefers _FILE over _PATH', function (): void {
    $_SERVER['DB_PASSWORD_FILE'] = secret($this->directory, 'file', 'from-file');
    $_SERVER['DB_PASSWORD_PATH'] = secret($this->directory, 'path', 'from-path');

    expect((new SecretFileAdapter)->read('DB_PASSWORD')->get())->toBe('from-file');
});

it('never resolves a name that is itself a suffix variable', function (): void {
    $_SERVER['DB_PASSWORD_FILE_FILE'] = secret($this->directory, 'db', 'nope');

    expect((new SecretFileAdapter)->read('DB_PASSWORD_FILE')->isDefined())->toBeFalse();

    unset($_SERVER['DB_PASSWORD_FILE_FILE']);
});

it('reads from the systemd credentials directory', function (): void {
    $_SERVER['CREDENTIALS_DIRECTORY'] = $this->directory;

    secret($this->directory, 'DB_PASSWORD', 's3cret');

    expect((new SecretFileAdapter)->read('DB_PASSWORD')->get())->toBe('s3cret');
});

it('reads from the nomad secrets directory', function (): void {
    $_SERVER['NOMAD_SECRETS_DIR'] = $this->directory;

    secret($this->directory, 'DB_PASSWORD', 's3cret');

    expect((new SecretFileAdapter)->read('DB_PASSWORD')->get())->toBe('s3cret');
});

it('falls back to the lowercase filename', function (): void {
    $_SERVER['CREDENTIALS_DIRECTORY'] = $this->directory;

    secret($this->directory, 'db_password', 's3cret');

    expect((new SecretFileAdapter)->read('DB_PASSWORD')->get())->toBe('s3cret');
});

it('searches the directories in order', function (): void {
    $nomad = $this->directory.'/nomad';

    mkdir($nomad, 0700, true);

    $_SERVER['CREDENTIALS_DIRECTORY'] = $this->directory;
    $_SERVER['NOMAD_SECRETS_DIR'] = $nomad;

    secret($this->directory, 'DB_PASSWORD', 'from-systemd');
    secret($nomad, 'DB_PASSWORD', 'from-nomad');

    expect((new SecretFileAdapter)->read('DB_PASSWORD')->get())->toBe('from-systemd');

    unlink($nomad.'/DB_PASSWORD');
    rmdir($nomad);
});

it('trims trailing newlines only', function (): void {
    $_SERVER['DB_PASSWORD_FILE'] = secret($this->directory, 'db', "  pa ss  \r\n");

    expect((new SecretFileAdapter)->read('DB_PASSWORD')->get())->toBe('  pa ss  ');
});

it('returns an empty string for an empty secret file', function (): void {
    $_SERVER['DB_PASSWORD_FILE'] = secret($this->directory, 'db', '');

    $value = (new SecretFileAdapter)->read('DB_PASSWORD');

    expect($value->isDefined())->toBeTrue()
        ->and($value->get())->toBe('');
});

it('returns nothing when no secret file exists', function (): void {
    $_SERVER['CREDENTIALS_DIRECTORY'] = $this->directory;

    expect((new SecretFileAdapter)->read('DB_PASSWORD')->isDefined())->toBeFalse();
});

it('refuses names that could traverse out of the directory', function (): void {
    $_SERVER['CREDENTIALS_DIRECTORY'] = $this->directory;

    secret($this->directory, 'db', 's3cret');

    expect((new SecretFileAdapter)->read('../'.basename($this->directory).'/db')->isDefined())->toBeFalse();
});

it('fails loudly when a suffix variable points at a missing file', function (): void {
    $_SERVER['DB_PASSWORD_FILE'] = $this->directory.'/absent';

    expect(fn (): Option => (new SecretFileAdapter)->read('DB_PASSWORD'))
        ->toThrow(SecretFileException::class, 'does not exist');
});

it('ignores variables a remote client can inject through $_SERVER', function (): void {
    $_SERVER['HTTP_PROXY_FILE'] = secret($this->directory, 'proxy', 'http://attacker.example');

    expect((new SecretFileAdapter)->read('HTTP_PROXY')->isDefined())->toBeFalse();

    unset($_SERVER['HTTP_PROXY_FILE']);
});

it('ignores the same injection behind an Apache redirect prefix', function (): void {
    $_SERVER['REDIRECT_HTTP_PROXY_FILE'] = secret($this->directory, 'proxy', 'http://attacker.example');

    expect((new SecretFileAdapter)->read('REDIRECT_HTTP_PROXY')->isDefined())->toBeFalse();

    unset($_SERVER['REDIRECT_HTTP_PROXY_FILE']);
});

it('refuses a relative path', function (): void {
    $_SERVER['DB_PASSWORD_FILE'] = 'secrets/db';

    expect(fn (): Option => (new SecretFileAdapter)->read('DB_PASSWORD'))
        ->toThrow(SecretFileException::class, 'must be absolute');
});

it('refuses a file larger than one mebibyte', function (): void {
    $_SERVER['DB_PASSWORD_FILE'] = secret($this->directory, 'db', str_repeat('a', 1048577));

    expect(fn (): Option => (new SecretFileAdapter)->read('DB_PASSWORD'))
        ->toThrow(SecretFileException::class, 'is larger than 1048576 bytes');
});

it('accepts a file at exactly the limit', function (): void {
    $_SERVER['DB_PASSWORD_FILE'] = secret($this->directory, 'db', str_repeat('a', 1048576));

    expect((new SecretFileAdapter)->read('DB_PASSWORD')->get())->toHaveLength(1048576);
});

it('separates an unreadable file from a missing one', function (): void {
    $_SERVER['DB_PASSWORD_FILE'] = $path = secret($this->directory, 'db', 's3cret');

    chmod($path, 0000);

    expect(fn (): Option => (new SecretFileAdapter)->read('DB_PASSWORD'))
        ->toThrow(SecretFileException::class, 'is not readable');

    chmod($path, 0600);
})->skipOnWindows()->skip(
    fn (): bool => ! function_exists('posix_geteuid') || posix_geteuid() === 0,
    'root reads every file regardless of mode',
);

it('resolves each name once', function (): void {
    $path = secret($this->directory, 'db', 'first');

    $_SERVER['DB_PASSWORD_FILE'] = $path;

    $adapter = new SecretFileAdapter;

    expect($adapter->read('DB_PASSWORD')->get())->toBe('first');

    file_put_contents($path, 'second');

    expect($adapter->read('DB_PASSWORD')->get())->toBe('first');
});

it('accepts writes without breaking the repository writer chain', function (): void {
    $adapter = new SecretFileAdapter;

    expect($adapter->write('DB_PASSWORD', 's3cret'))->toBeTrue()
        ->and($adapter->delete('DB_PASSWORD'))->toBeTrue();
});
