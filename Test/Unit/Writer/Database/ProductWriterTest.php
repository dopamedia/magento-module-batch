<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 13.10.17
 */

namespace Dopamedia\Batch\Test\Unit\Writer\Database;

use Dopamedia\Batch\Model\Import\Source\ArraySource;
use Dopamedia\Batch\Model\Import\Source\ArraySourceFactory;
use Dopamedia\Batch\Writer\Database\ProductWriter;
use Dopamedia\PhpBatch\StepExecutionInterface;
use Magento\ImportExport\Model\ImportFactory as ImportModelFactory;
use Magento\ImportExport\Model\Import as ImportModel;
use Dopamedia\Batch\ArrayConverter\FlatToStandard\Products as ProductsArrayConverter;
use PHPUnit\Framework\TestCase;

class ProductWriterTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ProductsArrayConverter
     */
    protected $productsArrayConverterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ImportModelFactory
     */
    protected $importModelFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ImportModel
     */
    protected $importModelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ArraySourceFactory
     */
    protected $arraySourceFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ArraySource
     */
    protected $arraySourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StepExecutionInterface
     */
    protected $stepExecutionMock;

    /**
     * @var ProductWriter
     */
    protected $productWriter;

    protected function setUp()
    {
        $this->productsArrayConverterMock = $this->createMock(ProductsArrayConverter::class);

        $this->importModelFactoryMock = $this->getMockBuilder(ImportModelFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->importModelMock = $this->createMock(ImportModel::class);

        $this->arraySourceFactoryMock = $this->getMockBuilder(ArraySourceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->arraySourceMock = $this->createMock(ArraySource::class);

        $this->stepExecutionMock = $this->createMock(StepExecutionInterface::class);

        $this->productWriter = new ProductWriter(
            $this->productsArrayConverterMock,
            $this->importModelFactoryMock,
            $this->arraySourceFactoryMock
        );

        $this->productWriter->setStepExecution($this->stepExecutionMock);

    }

    public function testWriteFailsValidation()
    {
        $items = [['sku' => 'sku']];

        $this->productsArrayConverterMock->expects($this->once())
            ->method('convert')
            ->with($items)
            ->willReturn($items);

        $this->importModelFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->importModelMock);

        $this->importModelMock->expects($this->once())
            ->method('setData')
            ->willReturn($this->importModelMock);

        $this->arraySourceFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => $items])
            ->willReturn($this->arraySourceMock);

        $this->importModelMock->expects($this->once())
            ->method('validateSource')
            ->with($this->arraySourceMock)
            ->willReturn(false);

        $this->importModelMock->expects($this->once())
            ->method('getFormatedLogTrace')
            ->willReturn('the log trace');

        $this->stepExecutionMock->expects($this->once())
            ->method('addError')
            ->with('the log trace');

        $this->productWriter->write($items);
    }

    public function testWriteWithFalseImportResult()
    {
        $items = [['sku' => 'sku']];

        $this->productsArrayConverterMock->expects($this->once())
            ->method('convert')
            ->with($items)
            ->willReturn($items);

        $this->importModelFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->importModelMock);

        $this->importModelMock->expects($this->once())
            ->method('setData')
            ->willReturn($this->importModelMock);

        $this->arraySourceFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => $items])
            ->willReturn($this->arraySourceMock);

        $this->importModelMock->expects($this->once())
            ->method('validateSource')
            ->with($this->arraySourceMock)
            ->willReturn(true);

        $this->importModelMock->expects($this->once())
            ->method('importSource')
            ->willReturn(false);

        $this->importModelMock->expects($this->once())
            ->method('getFormatedLogTrace')
            ->willReturn('the log trace');

        $this->stepExecutionMock->expects($this->once())
            ->method('addError')
            ->with('the log trace');

        $this->productWriter->write($items);
    }

    public function testWriteThrowsException()
    {
        $items = [['sku' => 'sku']];

        $this->productsArrayConverterMock->expects($this->once())
            ->method('convert')
            ->with($items)
            ->willReturn($items);

        $this->importModelFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->importModelMock);

        $this->importModelMock->expects($this->once())
            ->method('setData')
            ->willReturn($this->importModelMock);

        $this->arraySourceFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => $items])
            ->willReturn($this->arraySourceMock);

        $this->importModelMock->expects($this->once())
            ->method('validateSource')
            ->with($this->arraySourceMock)
            ->willReturn(true);

        $this->importModelMock->expects($this->once())
            ->method('importSource')
            ->willThrowException(new \Exception('exception message'));

        $this->importModelMock->expects($this->once())
            ->method('getFormatedLogTrace')
            ->willReturn('the log trace');

        $this->stepExecutionMock->expects($this->exactly(2))
            ->method('addError')
            ->withConsecutive(
                ['exception message'],
                ['the log trace']
            );

        $this->productWriter->write($items);
    }

    public function testWrite()
    {
        $items = [['sku' => 'sku']];

        $this->productsArrayConverterMock->expects($this->once())
            ->method('convert')
            ->with($items)
            ->willReturn($items);

        $this->importModelFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->importModelMock);

        $this->importModelMock->expects($this->once())
            ->method('setData')
            ->willReturn($this->importModelMock);

        $this->arraySourceFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => $items])
            ->willReturn($this->arraySourceMock);

        $this->importModelMock->expects($this->once())
            ->method('validateSource')
            ->with($this->arraySourceMock)
            ->willReturn(true);

        $this->importModelMock->expects($this->once())
            ->method('importSource');

        $this->importModelMock->expects($this->once())
            ->method('getUpdatedItemsCount')
            ->willReturn(10);

        $this->importModelMock->expects($this->once())
            ->method('getCreatedItemsCount')
            ->willReturn(20);

        $this->importModelMock->expects($this->once())
            ->method('getDeletedItemsCount')
            ->willReturn(30);

        $this->stepExecutionMock->expects($this->exactly(3))
            ->method('addSummaryInfo')
            ->withConsecutive(
                ['update', 10],
                ['create', 20],
                ['delete', 30]
            );

        $this->productWriter->write($items);
    }
}
