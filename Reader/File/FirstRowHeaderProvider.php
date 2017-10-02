<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 02.10.17
 */

namespace Dopamedia\Batch\Reader\File;

use Box\Spout\Reader\SheetInterface;

/**
 * Class FirstRowHeaderProvider
 * @package Dopamedia\Batch\Reader\File
 */
class FirstRowHeaderProvider implements HeaderProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getHeaders(SheetInterface $sheet): array
    {
        return $sheet->getRowIterator()->current();
    }

    /**
     * @return bool
     * @codeCoverageIgnore
     */
    public function processFirstRow(): bool
    {
        return false;
    }
}