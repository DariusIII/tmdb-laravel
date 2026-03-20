<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tmdb\Laravel\Adapters\Tests;

use Illuminate\Contracts\Events\Dispatcher as LaravelDispatcher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyDispatcher;
use Tmdb\Laravel\Adapters\EventDispatcherAdapter;

abstract class AbstractEventDispatcherTestCase extends TestCase
{
    public const EVENT = 'foo';

    protected EventDispatcherAdapter $dispatcher;

    /** @var LaravelDispatcher&MockObject */
    protected LaravelDispatcher $laravel;

    /** @var SymfonyDispatcher&MockObject */
    protected SymfonyDispatcher $symfony;

    protected function setUp(): void
    {
        $this->dispatcher = $this->createEventDispatcher();
    }

    abstract protected function createEventDispatcher(): EventDispatcherAdapter;

    public function testItDispatchesEventsThroughBothLaravelAndSymfony(): void
    {
        $event = new \stdClass();

        $this->laravel
            ->expects($this->once())
            ->method('dispatch')
            ->with($event, static::EVENT);

        $this->symfony
            ->expects($this->once())
            ->method('dispatch')
            ->with($event, static::EVENT)
            ->willReturn($event);

        $this->assertSame($event, $this->dispatcher->dispatch($event, static::EVENT));
    }

    public function testItAddsListenersToTheSymfonyDispatcher(): void
    {
        $listener = [$this, 'listener'];

        $this->expectNoLaravelDispatcherInteraction();

        $this->symfony
            ->expects($this->once())
            ->method('addListener')
            ->with(static::EVENT, $listener, 1);

        $this->dispatcher->addListener(static::EVENT, $listener, 1);
    }

    public function testItAddsASubscriberToTheSymfonyDispatcher(): void
    {
        $subscriber = $this->createSubscriber();

        $this->expectNoLaravelDispatcherInteraction();

        $this->symfony
            ->expects($this->once())
            ->method('addSubscriber')
            ->with($subscriber);

        $this->dispatcher->addSubscriber($subscriber);
    }

    public function testItRemovesListenersFromTheSymfonyDispatcher(): void
    {
        $listener = [$this, 'listener'];

        $this->expectNoLaravelDispatcherInteraction();

        $this->symfony
            ->expects($this->once())
            ->method('removeListener')
            ->with(static::EVENT, $listener);

        $this->dispatcher->removeListener(static::EVENT, $listener);
    }

    public function testItRemovesSubscriptionsFromTheSymfonyDispatcher(): void
    {
        $subscriber = $this->createSubscriber();

        $this->expectNoLaravelDispatcherInteraction();

        $this->symfony
            ->expects($this->once())
            ->method('removeSubscriber')
            ->with($subscriber);

        $this->dispatcher->removeSubscriber($subscriber);
    }

    /**
     * We are not checking Laravel's listeners as its interface does not contain a getListeners function.
     */
    public function testItGetsListenersFromTheSymfonyDispatcher(): void
    {
        $this->expectNoLaravelDispatcherInteraction();

        $this->symfony
            ->expects($this->once())
            ->method('getListeners')
            ->with(static::EVENT)
            ->willReturn(['foo']);

        $this->assertEquals(['foo'], $this->dispatcher->getListeners(static::EVENT));
    }

    public function testItAsksTheSymfonyDispatcherIfItHasAListener(): void
    {
        $this->laravel
            ->expects($this->never())
            ->method('hasListeners');

        $this->symfony
            ->expects($this->once())
            ->method('hasListeners')
            ->with(static::EVENT)
            ->willReturn(true);

        $this->assertTrue($this->dispatcher->hasListeners(static::EVENT));
    }

    public function testItAsksTheLaravelDispatcherIfItHasAListener(): void
    {
        $this->symfony
            ->expects($this->once())
            ->method('hasListeners')
            ->with(static::EVENT)
            ->willReturn(false);

        $this->laravel
            ->expects($this->once())
            ->method('hasListeners')
            ->with(static::EVENT)
            ->willReturn(true);

        $this->assertTrue($this->dispatcher->hasListeners(static::EVENT));
    }

    public function testItAsksBothTheSymfonyAndLaravelDispatcherIfItHasAListener(): void
    {
        $this->symfony
            ->expects($this->once())
            ->method('hasListeners')
            ->with(static::EVENT)
            ->willReturn(false);

        $this->laravel
            ->expects($this->once())
            ->method('hasListeners')
            ->with(static::EVENT)
            ->willReturn(false);

        $this->assertFalse($this->dispatcher->hasListeners(static::EVENT));
    }

    /**
     * We are not checking Laravel's listeners as its interface does not contain a getListenerPriority function.
     */
    public function testItAsksTheSymfonyDispatcherForAListenersPriority(): void
    {
        $listener = [$this, 'listener'];

        $this->expectNoLaravelDispatcherInteraction();

        $this->symfony
            ->expects($this->once())
            ->method('getListenerPriority')
            ->with(static::EVENT, $listener)
            ->willReturn(100);

        $this->assertEquals(100, $this->dispatcher->getListenerPriority(static::EVENT, $listener));
    }

    public function listener(): void
    {
    }

    private function expectNoLaravelDispatcherInteraction(): void
    {
        $this->laravel
            ->expects($this->never())
            ->method('dispatch');

        $this->laravel
            ->expects($this->never())
            ->method('hasListeners');
    }

    private function createSubscriber(): EventSubscriberInterface
    {
        return new class implements EventSubscriberInterface {
            public static function getSubscribedEvents(): array
            {
                return [];
            }
        };
    }
}

