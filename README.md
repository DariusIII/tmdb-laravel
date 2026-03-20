# Laravel Package for TMDB API Wrapper

[![License](https://poser.pugx.org/dariusiii/tmdb-laravel/license.png)](https://packagist.org/packages/dariusiii/tmdb-laravel)
[![Build Status](https://travis-ci.org/dariusiii/tmdb-laravel.svg)](https://travis-ci.org/dariusiii/tmdb-laravel)
[![Code Coverage](https://scrutinizer-ci.com/g/dariusiii/tmdb-laravel/badges/coverage.png)](https://scrutinizer-ci.com/g/dariusiii/tmdb-laravel/)

A Laravel package that provides easy access to the [php-tmdb/api](https://github.com/php-tmdb/api) TMDB (The Movie Database) API wrapper.
This package comes with a service provider that configures the `Tmdb\Client` and registers it to the IoC container.
Supports Laravel 8 through 13.

[![Latest Stable Version](https://poser.pugx.org/dariusiii/tmdb-laravel/v/stable.svg)](https://packagist.org/packages/dariusiii/tmdb-laravel)
[![Latest Unstable Version](https://poser.pugx.org/dariusiii/tmdb-laravel/v/unstable.svg)](https://packagist.org/packages/dariusiii/tmdb-laravel)
[![Total Downloads](https://poser.pugx.org/dariusiii/tmdb-laravel/downloads.svg)](https://packagist.org/packages/dariusiii/tmdb-laravel)

## Installation

Install Composer

```
$ curl -sS https://getcomposer.org/installer | php
$ sudo mv composer.phar /usr/local/bin/composer
```

Add the following to your require block in `composer.json` config

```
"dariusiii/tmdb-laravel": "^2.0"
```

or just run the following command in your project:

```
composer require dariusiii/tmdb-laravel
```

## Configuration

Laravel will auto-discover the package on supported versions. If you prefer to register it manually, add the service provider to `config/app.php`:

```php
'providers' => array(
    // other service providers

    'Tmdb\Laravel\TmdbServiceProvider',
)
```

Then publish the configuration file:

```
php artisan vendor:publish --provider="Tmdb\Laravel\TmdbServiceProvider"
```

Next you can modify the generated configuration file `tmdb.php` accordingly.

That's all! Fire away!

## Usage

We can choose to either use the `Tmdb` Facade, or to use dependency injection.

## Development

Run the package quality checks locally with Composer scripts:

```bash
composer test
composer phpstan
```

### Facade example

The example below shows how you can use the `Tmdb` facade.
If you don't want to keep adding the `use Tmdb\Laravel\Facades\Tmdb;` statement in your files, then you can also add the facade as an alias in `config/app.php` file.

```php
use Tmdb\Laravel\Facades\Tmdb;

class MoviesController {

    function show($id)
    {
        // returns information of a movie
        return Tmdb::getMoviesApi()->getMovie($id);
    }
}
```

### Dependency injection example

```php
use Tmdb\Repository\MovieRepository;

class MoviesController {

    private $movies;

    function __construct(MovieRepository $movies)
    {
        $this->movies = $movies;
    }

    function index()
    {
        // returns information of a movie
        return $this->movies->getPopular();
    }
}
```

### Listening to events

We can easily listen to events that are dispatched using the Laravel event dispatcher that we're familiar with.
The following example listens to any request that is made and logs a message.

```php
use Event;
use Illuminate\Support\Facades\Log;
use Tmdb\Event\RequestEvent;

Event::listen(RequestEvent::class, function (RequestEvent $event) {
    Log::info("A request was made to TMDB");
    // do stuff with $event
});
```

You can also register the same listener in your application's `EventServiceProvider`.

### Image helper

You can easily use the `ImageHelper` by using dependency injection. The following example shows how to show the poster image of the 20 most popular movies.

```php
namespace App\Http\Controllers;

use Tmdb\Helper\ImageHelper;
use Tmdb\Repository\MovieRepository;

class WelcomeController extends Controller {

    private $movies;
    private $helper;

    public function __construct(MovieRepository $movies, ImageHelper $helper)
    {
        $this->movies = $movies;
        $this->helper = $helper;
    }

    /**
     * Show the application welcome screen to the user.
     *
     * @return Response
     */
    public function index()
    {
        $popular = $this->movies->getPopular();

        foreach ($popular as $movie)
        {
            $image = $movie->getPosterImage();
            echo ($this->helper->getHtml($image, 'w154', 260, 420));
        }
    }

}
```

The `Configuration` used by the `Tmdb\Helper\ImageHelper` is automatically loaded by the IoC container.

### Customizing outgoing requests

You can customize outgoing requests by listening for `BeforeRequestEvent` in one of your application's service providers.

```php
namespace App\Providers;

use Event;
use Illuminate\Support\ServiceProvider;
use Tmdb\Event\BeforeRequestEvent;

class TmdbServiceProvider extends ServiceProvider {

    /**
     * Customize TMDB requests before they are sent.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen(BeforeRequestEvent::class, function (BeforeRequestEvent $event) {
            $request = $event->getRequest()->withHeader('Accept-Language', 'nl-NL');

            $event->setRequest($request);
        });
    }

    /**
     * Register services
     * @return void
     */
    public function register()
    {
        // register any services that you need
    }
}
```

**For all all other interactions take a look at [php-tmdb/api](https://github.com/php-tmdb/api).**
