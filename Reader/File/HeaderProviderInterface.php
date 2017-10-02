<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 02.10.17
 */

namespace Dopamedia\Batch\Reader\File;

use Box\Spout\Reader\SheetInterface;

/**
 * Interface HeaderProviderInterface
 * @package Dopamedia\Batch\Reader\File
 */
interface HeaderProviderInterface
{
    /**
     * @param SheetInterface $fileIterator
     * @return array
     */
    public function getHeaders(SheetInterface $fileIterator): array;

    /**
     * @return bool
     */
    public function processFirstRow(): bool;
}