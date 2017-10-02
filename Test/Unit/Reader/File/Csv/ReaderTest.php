<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 02.10.17
 */

namespace Dopamedia\Batch\Test\Unit\Reader\File\Csv;

use Dopamedia\Batch\Reader\File\Csv\Reader;
use Dopamedia\Batch\Reader\File\FileIteratorInterfaceFactory;
use Dopamedia\Batch\Reader\File\FlatFileIterator;
use Dopamedia\Batch\Reader\File\HeaderProviderInterface;
use Dopamedia\PhpBatch\Item\InvalidItemException;
use Dopamedia\PhpBatch\Job\JobParameters;
use Dopamedia\PhpBatch\StepExecutionInterface;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FileIteratorInterfaceFactory
     */
    protected $fileIteratorFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FlatFileIterator
     */
    protected $flatFileIteratorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|HeaderProviderInterface
     */
    protected $headerProviderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StepExecutionInterface
     */
    protected $stepExecutionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobParameters
     */
    protected $jobParametersMock;

    /**
     * @var string
     */
    protected $filePath;

    protected function setUp()
    {
        $this->fileIteratorFactoryMock = $this->getMockBuilder(FileIteratorInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->flatFileIteratorMock = $this->createMock(FlatFileIterator::class);

        $this->headerProviderMock = $this->createMock(HeaderProviderInterface::class);

        $this->fileIteratorFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->flatFileIteratorMock);

        $this->stepExecutionMock = $this->createMock(StepExecutionInterface::class);

        $this->jobParametersMock = $this->createMock(JobParameters::class);

        $this->filePath = realpath(__DIR__) . '/ReaderTest/_files';
    }

    public function testReadIncrementsSummaryInfo()
    {
        $reader = new Reader($this->fileIteratorFactoryMock, $this->headerProviderMock);

        $this->stepExecutionMock->expects($this->once())
            ->method('getJobParameters')
            ->willReturn($this->jobParametersMock);

        $reader->setStepExecution($this->stepExecutionMock);

        $this->flatFileIteratorMock->expects($this->once())
            ->method('valid')
            ->willReturn(true);

        $this->stepExecutionMock->expects($this->once())
            ->method('incrementSummaryInfo')
            ->with('item_position');

        $this->assertNull($reader->read());
    }

    public function testReadReturnsNull()
    {
        $reader = new Reader($this->fileIteratorFactoryMock, $this->headerProviderMock);

        $this->stepExecutionMock->expects($this->once())
            ->method('getJobParameters')
            ->willReturn($this->jobParametersMock);

        $reader->setStepExecution($this->stepExecutionMock);

        $this->flatFileIteratorMock->expects($this->once())
            ->method('current')
            ->willReturn(null);

        $this->assertNull($reader->read());
    }

    public function testReadThrowsInvalidItemException()
    {
        $reader = new Reader($this->fileIteratorFactoryMock, $this->headerProviderMock);

        $this->stepExecutionMock->expects($this->once())
            ->method('getJobParameters')
            ->willReturn($this->jobParametersMock);

        $this->jobParametersMock->expects($this->at(3))
            ->method('get')
            ->with('filePath')
            ->willReturn('the/file/path');

        $reader->setStepExecution($this->stepExecutionMock);

        $this->flatFileIteratorMock->expects($this->once())
            ->method('current')
            ->willReturn(['first', 'second', 'third']);

        $this->flatFileIteratorMock->expects($this->once())
            ->method('getHeaders')
            ->willReturn(['first', 'second']);

        $this->stepExecutionMock->expects($this->once())
            ->method('getSummaryInfo')
            ->with('item_position')
            ->willReturn(1);

        $this->expectException(InvalidItemException::class);

        $reader->read();
    }

    public function testReadAddsMissingColumns()
    {
        $reader = new Reader($this->fileIteratorFactoryMock, $this->headerProviderMock);

        $this->stepExecutionMock->expects($this->once())
            ->method('getJobParameters')
            ->willReturn($this->jobParametersMock);

        $this->jobParametersMock->expects($this->at(3))
            ->method('get')
            ->with('filePath')
            ->willReturn('the/file/path');

        $reader->setStepExecution($this->stepExecutionMock);

        $this->flatFileIteratorMock->expects($this->once())
            ->method('current')
            ->willReturn(['first', 'second']);

        $this->flatFileIteratorMock->expects($this->once())
            ->method('getHeaders')
            ->willReturn(['first', 'second', 'third']);

        $this->assertEquals(
            ['first' => 'first', 'second' => 'second', 'third' => ''],
            $reader->read()
        );
    }

    public function testRead()
    {
        $reader = new Reader($this->fileIteratorFactoryMock, $this->headerProviderMock);

        $this->stepExecutionMock->expects($this->once())
            ->method('getJobParameters')
            ->willReturn($this->jobParametersMock);

        $this->jobParametersMock->expects($this->at(3))
            ->method('get')
            ->with('filePath')
            ->willReturn('the/file/path');

        $reader->setStepExecution($this->stepExecutionMock);

        $this->flatFileIteratorMock->expects($this->once())
            ->method('current')
            ->willReturn(['first', 'second']);

        $this->flatFileIteratorMock->expects($this->once())
            ->method('getHeaders')
            ->willReturn(['first', 'second']);

        $this->assertEquals(
            ['first' => 'first', 'second' => 'second'],
            $reader->read()
        );
    }
}
