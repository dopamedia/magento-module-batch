<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 13.10.17
 */

namespace Dopamedia\Batch\Test\Unit\ArrayConverter\FlatToStandard;

use Dopamedia\Batch\ArrayConverter\FlatToStandard\Products as ProductsArrayConverter;
use Dopamedia\Batch\ArrayConverter\FlatToStandard\Product as ProductArrayConverter;
use PHPUnit\Framework\TestCase;

class ProductsTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ProductArrayConverter
     */
    protected $productArrayConverterMock;

    /**
     * @var ProductsArrayConverter
     */
    protected $productsArrayConverter;

    protected function setUp()
    {
        $this->productArrayConverterMock = $this->createMock(ProductArrayConverter::class);
        $this->productsArrayConverter = new ProductsArrayConverter($this->productArrayConverterMock);
    }

    public function testConvert()
    {
        $this->productArrayConverterMock->expects($this->once())
            ->method('convert')
            ->willReturn([
                ['sku' => 'first', 'store_view_code' => 'first', 'name' => 'first name'],
                ['sku' => 'first', 'store_view_code' => 'second', 'name' => 'second name']
            ]);

        $this->assertEquals(
            [
                ['sku' => 'first', 'store_view_code' => 'first', 'name' => 'first name'],
                ['sku' => 'first', 'store_view_code' => 'second', 'name' => 'second name']
            ],
            $this->productsArrayConverter->convert([
                ['sku' => 'first', 'name--store_view_code-first' => 'first name', 'name--store_view_code-second' => 'second name']
            ])
        );
    }
}
