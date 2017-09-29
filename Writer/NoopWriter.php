<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 29.09.17
 */

namespace Dopamedia\Batch\Writer;

use Dopamedia\PhpBatch\Item\ItemWriterInterface;

/**
 * Class NoopWriter
 * @package Dopamedia\Batch\Writer
 */
class NoopWriter implements ItemWriterInterface
{
    /**
     * @inheritDoc
     */
    public function write(array $items)
    {
        return null;
    }
}