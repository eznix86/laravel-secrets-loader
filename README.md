<div align="center">
    <h1>Laravel Secrets Loader</h1>
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

Precedence, highest first:

1. A real environment variable
2. A secret file
3. `.env`
4. The default passed to `env()`

Lines 2 and 3 are the ones to notice. A secret file overrides `.env`, which is deliberate, since a mounted secret should beat a placeholder committed to the repository. It also means you cannot turn a file-backed value off from `.env`. Set a real environment variable for that.

Trailing newlines are stripped, while leading and inner whitespace is preserved. Files above 1 MiB are rejected, so a wrong mount cannot pull something enormous into memory during boot.

If `DB_PASSWORD_FILE` points at a file that is missing or unreadable, the package throws rather than letting your application boot with an empty credential. This happens while config is loading, before Laravel registers its exception handler, so over HTTP you will see a plain 500 instead of the usual error page. The message names both the path and the variable that pointed at it.

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
