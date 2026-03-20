<?php
/**
 * @package php-tmdb\laravel
 * @author Mark Redeman <markredeman@gmail.com>
 * @copyright (c) 2014, Mark Redeman
 */
namespace Tmdb\Laravel;

use Tmdb\Laravel\Adapters\EventDispatcherAdapter;
use Tmdb\Laravel\Adapters\EventDispatcherLaravel;
use Illuminate\Support\ServiceProvider;

class TmdbServiceProviderLaravel extends ServiceProvider {

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            $this->defaultConfig() => $this->app->configPath('tmdb.php'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->setupConfiguration();

        $this->app->bind(EventDispatcherAdapter::class, EventDispatcherLaravel::class);
    }

    /**
     * Get the TMDB configuration from the config repository
     *
     * @return array
     */
    /**
     * @return array{api_key: string}
     */
    public function config(): array
    {
        $config = $this->app['config']->get('tmdb');

        if (! is_array($config)) {
            return ['api_key' => ''];
        }

        return [
            'api_key' => isset($config['api_key']) ? (string) $config['api_key'] : '',
        ];
    }

    /**
     * Setup configuration
     *
     * @return  void
     */
    private function setupConfiguration(): void
    {
        $config = $this->defaultConfig();
        $this->mergeConfigFrom($config, 'tmdb');
    }

    /**
     * Returns the default configuration path
     *
     * @return string
     */
    private function defaultConfig(): string
    {
        return __DIR__ . '/config/tmdb.php';
    }
}
