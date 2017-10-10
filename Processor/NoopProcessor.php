<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 29.09.17
 */

namespace Dopamedia\Batch\Processor;

use Dopamedia\PhpBatch\Item\ItemProcessorInterface;

/**
 * Class NoopProcessor
 * @package Dopamedia\Batch\Processor
 */
class NoopProcessor implements ItemProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function process($item)
    {
        return $item;
    }

}