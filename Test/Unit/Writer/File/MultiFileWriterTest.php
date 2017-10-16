<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 16.10.17
 */

namespace Dopamedia\Batch\Test\Unit\Writer\File;

use Dopamedia\Batch\Writer\File\MultiFileWriter;
use Dopamedia\PhpBatch\Item\FlushableInterface;
use Dopamedia\PhpBatch\Item\InitializableInterface;
use Dopamedia\PhpBatch\Item\ItemWriterInterface;
use Dopamedia\PhpBatch\Step\StepExecutionAwareInterface;
use Dopamedia\PhpBatch\StepExecutionInterface;
use PHPUnit\Framework\TestCase;

class MultiFileWriterTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FileWriterFlushableInitializableStepExecutionAwareInterface
     */
    protected $fileWriterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StepExecutionInterface
     */
    protected $stepExecutionMock;

    /**
     * @var MultiFileWriter
     */
    protected $multiFileWriter;

    protected function setUp()
    {
        $this->fileWriterMock = $this->createMock(FileWriterFlushableInitializableStepExecutionAwareInterface::class);
        $this->stepExecutionMock = $this->createMock(StepExecutionInterface::class);
        $this->multiFileWriter = new MultiFileWriter($this->fileWriterMock);
        $this->multiFileWriter->setStepExecution($this->stepExecutionMock);
    }

    public function testInitializeFileWriter()
    {
        $this->fileWriterMock->expects($this->once())
            ->method('flush');

        $this->fileWriterMock->expects($this->once())
            ->method('initialize');

        $this->fileWriterMock->expects($this->once())
            ->method('setStepExecution')
            ->with($this->stepExecutionMock);

        $this->multiFileWriter->write([['item']]);
    }

    public function testWrite()
    {

        $this->fileWriterMock->expects($this->exactly(3))
            ->method('write')
            ->withConsecutive([['first']], [['second']], [['third']]);

        $this->multiFileWriter->write([['first'], ['second'], ['third']]);
    }
}

interface FileWriterFlushableInitializableStepExecutionAwareInterface extends
    ItemWriterInterface,
    FlushableInterface,
    InitializableInterface,
    StepExecutionAwareInterface
{

}
