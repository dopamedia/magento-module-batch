<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 29.09.17
 */

namespace Dopamedia\Batch\Adapter;

use Dopamedia\PhpBatch\Adapter\EventManagerAdapterInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;

/**
 * Class EventManagerAdapter
 * @package Dopamedia\Batch\Adapter
 */
class EventManagerAdapter implements EventManagerAdapterInterface
{
    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * EventManagerAdapter constructor.
     * @param EventManagerInterface $eventManager
     */
    public function __construct(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * @inheritDoc
     */
    public function attach(string $event, callable $callback, int $priority = 0): bool
    {
        $this->eventManager->dispatch($event, [$callback()]);

        return true;
    }
}