<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 13.10.17
 */

namespace Dopamedia\Batch\Test\Unit\ArrayConverter\FlatToStandard;

use Dopamedia\Batch\ArrayConverter\FlatToStandard\Product as ProductArrayConverter;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    /**
     * @var ProductArrayConverter
     */
    protected $productArrayConverter;

    protected function setUp()
    {
        $this->productArrayConverter = new ProductArrayConverter();
    }

    /**
     * @dataProvider convertDataProvider
     */
    public function testConvert(array $expected, array $data)
    {
        $this->assertEquals($expected, $this->productArrayConverter->convert($data));
    }

    public function convertDataProvider()
    {
        return [
            [
                [
                    [
                        'sku' => 'sku',
                        'name' => 'name'
                    ]
                ],
                [
                    'sku' => 'sku',
                    'name' => 'name'
                ]
            ],
            [
                [
                    [
                        'sku' => 'sku',
                        'name' => 'default name'
                    ],
                    [
                        'sku' => 'sku',
                        'name' => 'store view specific name',
                        'store_view_code' => 'first'
                    ]
                ],
                [
                    'sku' => 'sku',
                    'name' => 'default name',
                    'name--store_view-first' => 'store view specific name'
                ]
            ]
        ];
    }


}
