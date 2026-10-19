<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Process;

beforeEach(function (): void {
    $this->secret = tempnam(sys_get_temp_dir(), 'secret');

    file_put_contents($this->secret, "s3cret\n");
});

afterEach(function (): void {
    unlink($this->secret);
});

it('resolves a secret file in a process that only requires the composer autoloader', function (): void {
    $result = Process::path(dirname(__DIR__, 2))
        ->env(['MAIL_PASSWORD_FILE' => $this->secret])
        ->run([PHP_BINARY, '-r', <<<'PHP'
            require 'vendor/autoload.php';

            echo env('MAIL_PASSWORD');
            PHP]);

    expect($result->output())->toBe('s3cret', $result->errorOutput());
});

it('registers from its bootstrap file before the laravel helpers are loaded', function (): void {
    $result = Process::path(dirname(__DIR__, 2))
        ->env(['MAIL_PASSWORD_FILE' => $this->secret])
        ->run([PHP_BINARY, '-r', <<<'PHP'
            require 'vendor/composer/ClassLoader.php';

            $loader = new Composer\Autoload\ClassLoader;

            foreach (require 'vendor/composer/autoload_psr4.php' as $prefix => $paths) {
                $loader->setPsr4($prefix, $paths);
            }

            $loader->addClassMap(require 'vendor/composer/autoload_classmap.php');
            $loader->register();

            require 'src/bootstrap.php';

            echo function_exists('value') ? 'helpers loaded' : Illuminate\Support\Env::getRepository()->get('MAIL_PASSWORD');
            PHP]);

    expect($result->output())->toBe('s3cret', $result->errorOutput());
});
