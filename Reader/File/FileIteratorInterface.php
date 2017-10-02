<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 01.10.17
 */

namespace Dopamedia\Batch\Reader\File;

/**
 * Interface FileIteratorInterface
 * @package Dopamedia\Batch\Reader\File
 */
interface FileIteratorInterface extends \Iterator
{
    /**
     * @return array
     */
    public function getHeaders(): array;
}