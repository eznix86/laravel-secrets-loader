<?php

declare(strict_types=1);

use Eznix86\LaravelSecretsLoader\SecretFileAdapter;
use Eznix86\LaravelSecretsLoader\SecretsLoader;
use Illuminate\Support\Env;

beforeEach(function (): void {
    $this->directory = sys_get_temp_dir().'/secrets-'.bin2hex(random_bytes(6));

    mkdir($this->directory, 0700, true);

    Env::extend(static fn (): SecretFileAdapter => new SecretFileAdapter, SecretsLoader::NAME);
});

afterEach(function (): void {
    foreach (glob($this->directory.'/*') ?: [] as $file) {
        unlink($file);
    }

    rmdir($this->directory);

    unset($_SERVER['MAIL_PASSWORD_FILE'], $_SERVER['MAIL_PASSWORD'], $_ENV['MAIL_PASSWORD']);

    Env::extend(static fn (): SecretFileAdapter => new SecretFileAdapter, SecretsLoader::NAME);
});

it('resolves a secret file through env()', function (): void {
    $_SERVER['MAIL_PASSWORD_FILE'] = $this->directory.'/mail';

    file_put_contents($this->directory.'/mail', "s3cret\n");

    expect(Env::get('MAIL_PASSWORD'))->toBe('s3cret');
});

it('lets a real environment variable win over the secret file', function (): void {
    $_SERVER['MAIL_PASSWORD'] = 'from-environment';
    $_SERVER['MAIL_PASSWORD_FILE'] = $this->directory.'/mail';

    file_put_contents($this->directory.'/mail', 'from-file');

    expect(Env::get('MAIL_PASSWORD'))->toBe('from-environment');
});

it('falls back to the given default when no secret exists', function (): void {
    expect(Env::get('MAIL_PASSWORD', 'default'))->toBe('default');
});

it('registers itself once', function (): void {
    SecretsLoader::register();
    SecretsLoader::register();

    $_SERVER['MAIL_PASSWORD_FILE'] = $this->directory.'/mail';

    file_put_contents($this->directory.'/mail', 's3cret');

    expect(Env::get('MAIL_PASSWORD'))->toBe('s3cret');
});
