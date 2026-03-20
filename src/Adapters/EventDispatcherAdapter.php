<?php
/**
 * @package php-tmdb\laravel
 * @author Mark Redeman <markredeman@gmail.com>
 * @copyright (c) 2014, Mark Redeman
 */
namespace Tmdb\Laravel\Adapters;

use Illuminate\Contracts\Events\Dispatcher as LaravelDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyDispatcher;

/**
 * This adapter provides a Laravel integration for applications
 * using the Symfony EventDispatcherInterface
 * It passes any request on to a Symfony Dispatcher and only
 * uses the Laravel Dispatcher when dispatching events
 */
abstract class EventDispatcherAdapter implements SymfonyDispatcher
{

    /**
     * The Laravel Events Dispatcher
     * @var \Illuminate\Contracts\Events\Dispatcher | \Illuminate\Events\Dispatcher
     */
    protected LaravelDispatcher $laravelDispatcher;

    /**
     * The Symfony Event Dispatcher
     * @var  \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected SymfonyDispatcher $symfonyDispatcher;

    /**
     * Dispatches an event to all registered listeners.
     *
     * @param object $event The event to pass to the event handlers/listeners.
     * @param string|null $eventName The name of the event to dispatch. The name of
     *                          the event is the name of the method that is
     *                          invoked on listeners.
     * @return object
     * @api
     */
    public function dispatch(object $event, ?string $eventName = null): object
    {
        $this->laravelDispatcher->dispatch($event, $eventName);
        $this->symfonyDispatcher->dispatch($event, $eventName);

        return $event;
    }

    /**
     * Adds an event listener that listens on the specified events.
     *
     * @param string   $eventName The event to listen on
     * @param callable $listener  The listener
     * @param int      $priority  The higher this value, the earlier an event
     *                            listener will be triggered in the chain (defaults to 0)
     *
     * @api
     */
    public function addListener(string $eventName, callable $listener, int $priority = 0): void
    {
        $this->symfonyDispatcher->addListener($eventName, $listener, $priority);
    }

    /**
     * Adds an event subscriber.
     *
     * The subscriber is asked for all the events he is
     * interested in and added as a listener for these events.
     *
     * @param EventSubscriberInterface $subscriber The subscriber.
     *
     * @api
     */
    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->symfonyDispatcher->addSubscriber($subscriber);
    }

    /**
     * Removes an event listener from the specified events.
     *
     * @param string   $eventName The event to remove a listener from
     * @param callable $listener The listener to remove
     */
    public function removeListener(string $eventName, callable $listener): void
    {
        $this->symfonyDispatcher->removeListener($eventName, $listener);
    }

    /**
     * Removes an event subscriber.
     *
     * @param EventSubscriberInterface $subscriber The subscriber
     */
    public function removeSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->symfonyDispatcher->removeSubscriber($subscriber);
    }

    /**
     * Gets the listeners of a specific event or all listeners.
     *
     * @param string|null $eventName The name of the event
     *
     * @return array<callable[]|callable> The event listeners for the specified event, or all event listeners by event name
     */
    public function getListeners(?string $eventName = null): array
    {
        return $this->symfonyDispatcher->getListeners($eventName);
    }

    /**
     * Checks whether an event has any registered listeners.
     *
     * @param string|null $eventName The name of the event
     *
     * @return bool true if the specified event has any listeners, false otherwise
     */
    public function hasListeners(?string $eventName = null): bool
    {
        if ($this->symfonyDispatcher->hasListeners($eventName)) {
            return true;
        }

        if ($eventName === null) {
            return false;
        }

        return $this->laravelDispatcher->hasListeners($eventName);
    }

    /**
     * Gets the listener priority for a specific event.
     *
     * Returns null if the event or the listener does not exist.
     *
     * @param string   $eventName The name of the event
     * @param callable $listener  The listener
     *
     * @return int|null The event listener priority
     */
    public function getListenerPriority(string $eventName, callable $listener): ?int
    {
        return $this->symfonyDispatcher->getListenerPriority($eventName, $listener);
    }
}
