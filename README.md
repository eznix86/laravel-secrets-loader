<div align="center">
    <h1>Secrets Loader for Laravel</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/eznix86/laravel-secrets-loader"><img src="https://img.shields.io/packagist/v/eznix86/laravel-secrets-loader.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/eznix86/laravel-secrets-loader"><img src="https://img.shields.io/packagist/php-v/eznix86/laravel-secrets-loader.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/eznix86/laravel-secrets-loader"><img src="https://badge.laravel.cloud/badge/eznix86/laravel-secrets-loader?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/eznix86/laravel-secrets-loader/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/eznix86/laravel-secrets-loader/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/eznix86/laravel-secrets-loader"><img src="https://img.shields.io/packagist/dt/eznix86/laravel-secrets-loader.svg?style=flat-square" alt="Total Downloads"></a>
</p>

Resolve Laravel environment variables from secret files; Docker secrets, systemd credentials, and _FILE conventions.

## Installation

```bash
composer require eznix86/laravel-secrets-loader
```

That is the whole setup. The package registers itself through Composer's autoloader, before Laravel boots, so `env()` calls inside your config files already see file-backed secrets. There is nothing to publish and nothing to configure.

## Usage

Nothing in your application changes. Keep reading configuration the way you already do:

```php
'password' => env('DB_PASSWORD'),
```

`DB_PASSWORD` now resolves from the first of these that exists:

| Source | Example |
| --- | --- |
| The process environment | `DB_PASSWORD=s3cret` |
| The path in `DB_PASSWORD_FILE` | `DB_PASSWORD_FILE=/run/secrets/db` |
| The path in `DB_PASSWORD_PATH` | `DB_PASSWORD_PATH=/run/secrets/db` |
| `$CREDENTIALS_DIRECTORY/DB_PASSWORD` | systemd `LoadCredential=` |
| `$NOMAD_SECRETS_DIR/DB_PASSWORD` | Nomad |
| `/run/secrets/DB_PASSWORD` | Docker, Swarm, Podman |

Directory lookups also try the lowercase filename, so `/run/secrets/db_password` works too.

A real environment variable always wins, which keeps local overrides and `.env` files working exactly as before. Trailing newlines are stripped; leading and inner whitespace is preserved. If `DB_PASSWORD_FILE` points at a file that cannot be read, the package throws rather than letting your application boot with an empty credential.

### Docker Compose

```yaml
services:
  app:
    secrets: [db_password]
    environment:
      DB_PASSWORD_FILE: /run/secrets/db_password

secrets:
  db_password:
    file: ./db_password.txt
```

### systemd

```ini
[Service]
LoadCredential=DB_PASSWORD:/etc/myapp/db_password
ExecStart=/usr/bin/php /var/www/artisan queue:work
```

systemd exports `CREDENTIALS_DIRECTORY` on its own, so nothing else is needed.

### Configuration caching

`php artisan config:cache` evaluates `env()` once and writes the results into `bootstrap/cache/config.php`, so your secrets land in that file in plaintext and a rotated secret is not seen until the cache is rebuilt. That is how Laravel's config cache has always worked, but it is worth knowing before you mount a secret you never wanted written to disk. Either skip config caching, or rebuild it whenever secrets change.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Laravel Secrets Loader! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Bruno Bernard](https://github.com/eznix86)
- [All Contributors](../../contributors)

## License

Laravel Secrets Loader is open-sourced software licensed under the [MIT license](LICENSE.md).
