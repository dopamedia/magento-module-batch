<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 13.10.17
 */

namespace Dopamedia\Batch\ValueConverter;

/**
 * Interface ValueConverterInterface
 * @package Dopamedia\Batch\ValueConverter
 */
interface ValueConverterInterface
{
    /**
     * @param array $item
     * @return array
     */
    public function convert(array $item): array;
}