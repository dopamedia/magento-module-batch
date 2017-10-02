<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 02.10.17
 */

namespace Dopamedia\Batch\Processor\Denormalization;

use Dopamedia\PhpBatch\Item\FileInvalidItem;
use Dopamedia\PhpBatch\Item\InvalidItemException;
use Dopamedia\PhpBatch\Step\StepExecutionAwareInterface;
use Dopamedia\PhpBatch\Step\StepExecutionAwareTrait;

/**
 * Class AbstractProcessor
 * @package Dopamedia\Batch\Processor\Denormalization
 */
abstract class AbstractProcessor implements StepExecutionAwareInterface
{
    use StepExecutionAwareTrait;

    /**
     * @param array $item
     * @param string $message
     * @param \Exception|null $previousException
     * @throws InvalidItemException
     */
    protected function skipItemWithMessage(array $item, string $message, \Exception $previousException = null)
    {
        $this->stepExecution->incrementSummaryInfo('skip');

        $invalidItem = new FileInvalidItem(
            $item,
            ($this->stepExecution->getSummaryInfo('item_position'))
        );

        throw new InvalidItemException($message, $invalidItem, [], 0, $previousException);
    }
}