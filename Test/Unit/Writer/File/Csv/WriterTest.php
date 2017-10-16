<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 16.10.17
 */

namespace Dopamedia\Batch\Test\Unit\Writer\File\Csv;

use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Box\Spout\Writer\Csv\WriterFactory as CsvWriterFactory;
use Box\Spout\Writer\CSV\Writer as CsvWriter;
use Dopamedia\Batch\Writer\File\Csv\Writer;
use Dopamedia\PhpBatch\Job\JobParameters;
use Dopamedia\PhpBatch\StepExecutionInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class WriterTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Filesystem
     */
    protected $filesystemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StepExecutionInterface
     */
    protected $stepExecutionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobParameters
     */
    protected $jobParametersMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CsvWriterFactory
     */
    protected $csvWriterFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CsvWriter
     */
    protected $csvWriterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|GlobalFunctionsHelper
     */
    protected $globalFunctionsHelperMock;

    /**
     * @var Writer
     */
    protected $writer;

    protected function setUp()
    {
        $this->filesystemMock = $this->createMock(Filesystem::class);

        $this->stepExecutionMock = $this->createMock(StepExecutionInterface::class);

        $this->jobParametersMock = $this->createMock(JobParameters::class);

        $this->stepExecutionMock->expects($this->any())
            ->method('getJobParameters')
            ->willReturn($this->jobParametersMock);

        $this->csvWriterFactoryMock = $this->getMockBuilder(CsvWriterFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->csvWriterMock = $this->createMock(CsvWriter::class);

        $this->csvWriterFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->csvWriterMock);

        $this->globalFunctionsHelperMock = $this->createMock(GlobalFunctionsHelper::class);

        $this->writer = new Writer(
            $this->filesystemMock,
            $this->csvWriterFactoryMock,
            $this->globalFunctionsHelperMock
        );

        $this->writer->setStepExecution($this->stepExecutionMock);
    }

    public function testWriteCreatesDirectory()
    {
        $this->jobParametersMock->expects($this->any())
            ->method('get')
            ->with('filePath')
            ->willReturn('/the/export/directory/file.csv');

        $this->filesystemMock->expects($this->once())
            ->method('exists')
            ->with('/the/export/directory')
            ->willReturn(false);

        $this->filesystemMock->expects($this->once())
            ->method('mkdir')
            ->with('/the/export/directory');

        $this->writer->write([]);
    }

    public function testWriteCreatesFile()
    {
        $filePath = '/the/export/directory/file.csv';

        $data = [[['first', 'second', 'third'], ['first', 'second', 'third']]];

        $this->jobParametersMock->expects($this->any())
            ->method('get')
            ->with('filePath')
            ->willReturn($filePath);

        $this->csvWriterFactoryMock->expects($this->once())
            ->method('create');

        $this->csvWriterMock->expects($this->once())
            ->method('openToFile')
            ->with($filePath);

        $this->csvWriterMock->expects($this->once())
            ->method('addRows')
            ->with($data);

        $this->writer->write($data);
    }
}
