<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 29.09.17
 */

namespace Dopamedia\Batch\Reader;

use Dopamedia\PhpBatch\Item\ItemReaderInterface;

/**
 * Class NoopReader
 * @package Dopamedia\Batch\Reader
 */
class NoopReader implements ItemReaderInterface
{
    /**
     * @inheritDoc
     */
    public function read()
    {
        return null;
    }
}