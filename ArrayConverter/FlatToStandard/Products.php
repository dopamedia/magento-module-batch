<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 13.10.17
 */

namespace Dopamedia\Batch\ArrayConverter\FlatToStandard;

use Dopamedia\Batch\ArrayConverter\ArrayConverterInterface;
use Dopamedia\Batch\ArrayConverter\FlatToStandard\Product as ProductArrayConverter;

/**
 * Class Products
 * @package Dopamedia\Batch\ArrayConverter\FlatToStandard
 */
class Products implements ArrayConverterInterface
{
    /**
     * @var Product
     */
    private $productArrayConverter;

    /**
     * Products constructor.
     * @param Product $productArrayConverter
     */
    public function __construct(ProductArrayConverter $productArrayConverter)
    {
        $this->productArrayConverter = $productArrayConverter;
    }

    /**
     * @inheritDoc
     */
    public function convert(array $items): array
    {
        $convertedItems = [];

        foreach ($items as $item) {
            $convertedItems = array_merge(
                $convertedItems,
                $this->productArrayConverter->convert($item)
            );
        }
        
        return $convertedItems;
    }
}