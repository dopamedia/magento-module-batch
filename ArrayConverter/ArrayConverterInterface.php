<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 09.10.17
 */

namespace Dopamedia\Batch\ArrayConverter;

/**
 * Interface ArrayConverterInterface
 * @package Dopamedia\Batch\ArrayConverter
 */
interface ArrayConverterInterface
{
    /**
     * @param array $items
     * @return array
     */
    public function convert(array $items): array;
}