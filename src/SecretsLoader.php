<?php

declare(strict_types=1);

namespace Eznix86\LaravelSecretsLoader;

use Illuminate\Support\Env;

final class SecretsLoader
{
    public const string NAME = 'eznix86/laravel-secrets-loader';

    private static bool $registered = false;

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        self::$registered = true;

        Env::extend(static fn (): SecretFileAdapter => new SecretFileAdapter, self::NAME);
    }
}
