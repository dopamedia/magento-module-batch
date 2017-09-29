<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 29.09.17
 */

namespace Dopamedia\Batch\Test\Unit\Adapter;

use Dopamedia\Batch\Adapter\EventManagerAdapter;
use Dopamedia\PhpBatch\Event\EventInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use PHPUnit\Framework\TestCase;

class EventManagerAdapterTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EventManagerInterface
     */
    protected $eventManagerMock;

    /**
     * @var EventManagerAdapter
     */
    protected $eventManagerAdapter;

    protected function setUp()
    {
        $this->eventManagerMock = $this->createMock(EventManagerInterface::class);

        $this->eventManagerAdapter = new EventManagerAdapter($this->eventManagerMock);
    }

    public function testAttach()
    {

        $eventObject = new class implements EventInterface {};

        $callback = function() use ($eventObject) {
            return $eventObject;
        };

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with('event', [$eventObject]);


        $this->assertTrue($this->eventManagerAdapter->attach('event', $callback));
    }
}
