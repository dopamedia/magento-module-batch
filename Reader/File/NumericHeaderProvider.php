<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 02.10.17
 */

namespace Dopamedia\Batch\Reader\File;

use Box\Spout\Reader\SheetInterface;

/**
 * Class NumericHeaderProvider
 * @package Dopamedia\Batch\Reader\File
 */
class NumericHeaderProvider implements HeaderProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getHeaders(SheetInterface $fileIterator): array
    {
        return array_keys($fileIterator->getRowIterator()->current());
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function doProcessFirstRow(): bool
    {
        return true;
    }
}