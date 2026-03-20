<?php
/**
 * @package php-tmdb\laravel
 * @author Mark Redeman <markredeman@gmail.com>
 * @copyright (c) 2014, Mark Redeman
 */
namespace Tmdb\Laravel\Adapters\Tests;

use Illuminate\Contracts\Events\Dispatcher as LaravelDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyDispatcher;
use Tmdb\Laravel\Adapters\EventDispatcherLaravel as AdapterDispatcher;

class EventDispatcherLaravel5Test extends AbstractEventDispatcherTestCase
{
    protected function createEventDispatcher(): AdapterDispatcher
    {
        $this->laravel = $this->createMock(LaravelDispatcher::class);
        $this->symfony = $this->createMock(SymfonyDispatcher::class);

        return new AdapterDispatcher(
            $this->laravel,
            $this->symfony
        );
    }
}

