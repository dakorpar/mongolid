<?php
namespace Mongolid\Connection;

use Illuminate\Container\Container;
use Mockery as m;
use MongoDB\Client;
use Mongolid\Event\EventTriggerInterface;
use Mongolid\Event\EventTriggerService;
use Mongolid\TestCase;

class ManagerTest extends TestCase
{
    protected function tearDown()
    {
        $this->setProtected(Manager::class, 'singleton', null);
        parent::tearDown();
    }

    public function testShouldAddAndGetConnection()
    {
        // Set
        $manager = new Manager();
        $connection = m::mock(Connection::class);
        $rawConnection = m::mock(Client::class);

        // Expectations
        $connection->expects()
            ->getRawConnection()
            ->andReturn($rawConnection);

        // Actions
        $manager->setConnection($connection);

        // Assertions
        $this->assertSame($rawConnection, $manager->getConnection());
    }

    public function testShouldSetEventTrigger()
    {
        // Set
        $test = $this;
        $manager = new Manager();
        $container = m::mock(Container::class);
        $eventTrigger = m::mock(EventTriggerInterface::class);

        $this->setProtected($manager, 'container', $container);

        // Expectations
        $container->expects()
            ->instance(EventTriggerService::class, m::type(EventTriggerService::class))
            ->andReturnUsing(function ($class, $eventService) use ($test, $eventTrigger) {
                $test->assertSame(EventTriggerService::class, $class);
                $test->assertAttributeSame($eventTrigger, 'builder', $eventService);
            });

        // Actions
        $manager->setEventTrigger($eventTrigger);
    }

    public function testShouldInitializeOnce()
    {
        // Set
        $manager = new Manager();

        // Actions
        $this->callProtected($manager, 'init');

        // Assertions
        $this->assertAttributeSame($manager, 'singleton', Manager::class);
        $this->assertAttributeInstanceOf(Container::class, 'container', $manager);

        // Actions
        $container = $manager->container;
        $this->callProtected($manager, 'init');

        // Assertions
        // Initializes again to make sure that it will not instantiate a new container
        $this->assertAttributeSame($container, 'container', $manager);
    }
}
