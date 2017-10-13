<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 13.10.17
 */

namespace Dopamedia\Batch\Test\Unit\ValueConverter\Denormalization;

use Dopamedia\Batch\ValueConverter\Denormalization\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    /**
     * @dataProvider convertValueDataProvider
     */
    public function testConvertValue(array $expected, array $data)
    {
        $productValueConverter = new Product();
        $this->assertEquals($expected, $productValueConverter->convert($data));
    }

    public function convertValueDataProvider()
    {
        return [
            [
                ['additional_images' => 'first.jpg,second.jpg,third.jpg'],
                ['additional_images' => ['first.jpg', 'second.jpg', 'third.jpg']],
            ],
            [
                ['configurable_variations' => 'sku=first,size=34|sku=second,size=36|sku=third,size=38'],
                ['configurable_variations' => [
                        ['sku' => 'first', 'size' => '34'],
                        ['sku' => 'second', 'size' => '36'],
                        ['sku' => 'third', 'size' => '38']
                    ]
                ]
            ]
        ];
    }
}
