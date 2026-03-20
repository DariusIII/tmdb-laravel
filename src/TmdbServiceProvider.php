<?php
/**
 * @package php-tmdb\laravel
 * @author Mark Redeman <markredeman@gmail.com>
 * @copyright (c) 2014, Mark Redeman
 */
namespace Tmdb\Laravel;

use Illuminate\Support\ServiceProvider;
use Tmdb\Event\BeforeRequestEvent;
use Tmdb\Event\Listener\Request\AcceptJsonRequestListener;
use Tmdb\Event\Listener\Request\ApiTokenRequestListener;
use Tmdb\Event\Listener\Request\ContentTypeJsonRequestListener;
use Tmdb\Event\Listener\Request\UserAgentRequestListener;
use Tmdb\Event\Listener\RequestListener;
use Illuminate\Contracts\Foundation\Application;
use Tmdb\Event\RequestEvent;
use Tmdb\Laravel\TmdbServiceProviderLaravel;
use Tmdb\Client;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Tmdb\Laravel\Adapters\EventDispatcherAdapter;
use Tmdb\Token\Api\ApiToken;

class TmdbServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Actual provider
     *
     * @var \Illuminate\Support\ServiceProvider
     */
    protected $provider;

    /**
     * Construct the TMDB service provider
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);

        $this->registerProvider();
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->provider->boot();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Configure any bindings that are version dependent
        $this->provider->register();

        // Let the IoC container be able to make a Symfony event dispatcher
        $this->app->bind(
            EventDispatcherInterface::class,
            EventDispatcher::class
        );

        // Setup default configurations for the Tmdb Client
        $this->app->singleton(Client::class, function() {
            $config = $this->provider->config();

            $ed = $this->app->make(EventDispatcherAdapter::class);
            $client = new Client(
                [
                    'api_token' => new ApiToken($config['api_key']),
                    'event_dispatcher' =>
                        [
                            'adapter' => $ed
                        ],
                ]
            );
            /**
             * Required event listeners and events to be registered with the PSR-14 Event Dispatcher.
             */
            $requestListener = new RequestListener($client->getHttpClient(), $ed);
            $ed->addListener(RequestEvent::class, $requestListener);

            $apiTokenListener = new ApiTokenRequestListener($client->getToken());
            $ed->addListener(BeforeRequestEvent::class, $apiTokenListener);

            $acceptJsonListener = new AcceptJsonRequestListener();
            $ed->addListener(BeforeRequestEvent::class, $acceptJsonListener);

            $jsonContentTypeListener = new ContentTypeJsonRequestListener();
            $ed->addListener(BeforeRequestEvent::class, $jsonContentTypeListener);

            $userAgentListener = new UserAgentRequestListener();
            $ed->addListener(BeforeRequestEvent::class, $userAgentListener);
            return $client;
        });

        $this->app->alias(Client::class, 'tmdb');
    }

    /**
     * Register the ServiceProvider
     */
    private function registerProvider()
    {
        $app = $this->app;
        $this->provider = new TmdbServiceProviderLaravel($app);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Client::class, 'tmdb'];
    }
}
