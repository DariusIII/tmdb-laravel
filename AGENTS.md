# AGENTS.md

## Project overview
- This repository is a Laravel integration package for `php-tmdb/api`.
- Current compatibility in `composer.json`: PHP `>=8.3`, Laravel components `^8|^9|^10|^11|^12|^13`, and `php-tmdb/api` `^6.0`.
- Package entry points are package discovery metadata in `composer.json`, `src/TmdbServiceProvider.php`, and `src/Facades/Tmdb.php`.
- `composer.lock` is not tracked in this repository; update `composer.json` and verify with fresh installs instead.

## Architecture
- `src/TmdbServiceProvider.php` is the root service provider registered with Laravel package discovery.
- The root provider delegates framework-specific config/publishing work to `src/TmdbServiceProviderLaravel.php`.
- The root provider is responsible for binding the `Tmdb\Client` singleton and aliasing it as `tmdb`.
- `src/TmdbServiceProviderLaravel.php` merges `src/config/tmdb.php` into the app config and publishes that file as `config/tmdb.php`.
- `src/Facades/Tmdb.php` resolves the client by `Tmdb\Client::class`; keep facade access aligned with the container binding.

## Event dispatch integration
- `src/Adapters/EventDispatcherAdapter.php` is the key compatibility layer.
- The adapter forwards dispatches to both the Laravel dispatcher and the Symfony dispatcher, while listener registration/removal is delegated to Symfony.
- `src/Adapters/EventDispatcherLaravel.php` wires `Illuminate\Contracts\Events\Dispatcher` with `Symfony\Component\EventDispatcher\EventDispatcherInterface`.
- `src/TmdbServiceProvider.php` registers TMDB request listeners (`RequestListener`, `ApiTokenRequestListener`, JSON/content-type/user-agent listeners) on the adapter; if TMDB library event APIs change, review this file first.

## Editing guidance
- Preserve the public package entry points unless the task explicitly requires a breaking change:
  - provider class name: `Tmdb\Laravel\TmdbServiceProvider`
  - facade class: `Tmdb\Laravel\Facades\Tmdb`
  - config key: `tmdb`
- Prefer small compatibility updates over refactors; this package is intentionally thin and mostly binds/configures upstream classes.
- If you change supported Laravel or TMDB versions, update both `composer.json` and the relevant README compatibility/install text.
- If you change bindings in `src/TmdbServiceProvider.php`, keep `provides()` and the facade accessor in sync.

## Tests and validation
- Tests are PHPUnit 12-based.
- Static analysis uses `phpstan/phpstan` with `phpstan/phpstan-phpunit`; the project config is `phpstan.neon.dist` and currently runs at level 6 over `src/` and `tests/`.
- Test discovery is configured in `phpunit.xml` and only picks up files ending in `*Test.php`.
- Adapter behavior is covered in `tests/Adapters/AbstractEventDispatcherTestCase.php` and `tests/Adapters/EventDispatcherLaravel5Test.php`.
- Use native PHPUnit mocks in tests; PHPUnit 12 is stricter about mocks without expectations, so prefer explicit expectations or concrete stubs where no interaction is expected. Do not reintroduce legacy Prophecy-based helpers without also restoring the required setup.
- Before finishing a change, run:
  - `composer validate --no-interaction`
  - `php vendor/bin/phpunit --no-coverage`
  - `php vendor/bin/phpstan analyse`
- Contributor-friendly Composer scripts mirror the main checks: `composer test` runs PHPUnit and `composer phpstan` runs static analysis.
- If dependency constraints changed, also verify with a targeted Composer update, for example:
  - `composer update php-tmdb/api illuminate/support illuminate/events --with-all-dependencies --no-interaction`

## Documentation touchpoints
- `README.md` documents installation, provider registration, config publishing, facade usage, dependency injection, and event listening examples.
- Keep README examples aligned with current provider names and supported Laravel versions.

