<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 03.10.17
 */

namespace Dopamedia\Batch\Reader\Database;

use Dopamedia\PhpBatch\Item\InitializableInterface;
use Dopamedia\PhpBatch\Item\ItemReaderInterface;
use Dopamedia\PhpBatch\Step\StepExecutionAwareInterface;
use Dopamedia\PhpBatch\Step\StepExecutionAwareTrait;

/**
 * Class AbstractReader
 * @package Dopamedia\Batch\Reader\Database
 */
abstract class AbstractReader implements ItemReaderInterface, InitializableInterface, StepExecutionAwareInterface
{
    use StepExecutionAwareTrait;

    /**
     * @var bool
     */
    private $isExecuted = false;

    /**
     * @var null|\ArrayIterator
     */
    private $results;

    /**
     * @inheritdoc
     */
    public function read()
    {
        if ($this->isExecuted === false) {
            $this->isExecuted = true;
            $this->results = $this->getResults();
        }

        if (null !== $result = $this->results->current()) {
            $this->results->next();
            $this->stepExecution->incrementSummaryInfo('read');
        }

        return $result;
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function initialize(): void
    {
        $this->isExecuted = false;
    }

    /**
     * @return \ArrayIterator
     */
    abstract protected function getResults(): \ArrayIterator;
}