<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 16.10.17
 */

namespace Dopamedia\Batch\Test\Unit\Reader\File;

use Dopamedia\Batch\Reader\File\MultiFileReader;
use Dopamedia\PhpBatch\Item\InitializableInterface;
use Dopamedia\PhpBatch\Item\ItemReaderInterface;
use Dopamedia\PhpBatch\Job\JobParameters;
use Dopamedia\PhpBatch\Step\StepExecutionAwareInterface;
use Dopamedia\PhpBatch\StepExecutionInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class MultiFileReaderTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ItemReaderInterface
     */
    protected $fileReaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Finder
     */
    protected $finderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StepExecutionInterface
     */
    protected $stepExecutionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobParameters
     */
    protected $jobParametersMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Iterator
     */
    protected $filesIteratorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SplFileInfo
     */
    protected $splFileInfoMock;

    /**
     * @var MultiFileReader
     */
    protected $multiFileReader;

    protected function setUp()
    {
        $this->fileReaderMock = $this->createMock(ItemReaderInterface::class);

        $this->finderMock = $this->createMock(Finder::class);

        $this->stepExecutionMock = $this->createMock(StepExecutionInterface::class);

        $this->jobParametersMock = $this->createMock(JobParameters::class);

        $this->multiFileReader = new MultiFileReader($this->fileReaderMock, $this->finderMock);

        $this->filesIteratorMock = $this->createMock(\Iterator::class);

        $this->splFileInfoMock = $this->createMock(SplFileInfo::class);

        $this->multiFileReader->setStepExecution($this->stepExecutionMock);
    }

    public function testReadCreatesFilesIterator()
    {
        $this->stepExecutionMock->expects($this->once())
            ->method('getJobParameters')
            ->willReturn($this->jobParametersMock);

        $this->jobParametersMock->expects($this->once())
            ->method('get')
            ->with('filePattern')
            ->willReturn('the/directory/the_pattern_*.csv');

        $this->finderMock->expects($this->once())
            ->method('in')
            ->with('the/directory')
            ->willReturnSelf();

        $this->finderMock->expects($this->once())
            ->method('name')
            ->with('the_pattern_*.csv')
            ->willReturnSelf();

        $this->finderMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \AppendIterator());

        $this->multiFileReader->read();
    }

    public function testReadInitializesCurrentFileReader()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|DummyItemReaderStepExecutionAwareInitializableInterface $fileReaderMock */
        $fileReaderMock = $this->createMock(DummyItemReaderStepExecutionAwareInitializableInterface::class);

        $multiFileReader = new MultiFileReader($fileReaderMock, $this->finderMock);
        $multiFileReader->setStepExecution($this->stepExecutionMock);

        $this->stepExecutionMock->expects($this->any())
            ->method('getJobParameters')
            ->willReturn($this->jobParametersMock);

        $this->jobParametersMock->expects($this->once())
            ->method('get')
            ->with('filePattern');

        $this->finderMock->expects($this->once())
            ->method('in')
            ->willReturnSelf();

        $this->finderMock->expects($this->once())
            ->method('name')
            ->willReturnSelf();

        $this->finderMock->expects($this->once())
            ->method('getIterator')
            ->willReturn($this->filesIteratorMock);

        $this->filesIteratorMock->expects($this->once())
            ->method('current')
            ->willReturn($this->splFileInfoMock);

        $fileReaderMock->expects($this->once())
            ->method('setStepExecution');

        $fileReaderMock->expects($this->once())
            ->method('initialize');

        $this->splFileInfoMock->expects($this->once())
            ->method('getRealPath')
            ->willReturn('the/directory/the_pattern_123.csv');

        $this->jobParametersMock->expects($this->once())
            ->method('set')
            ->with('filePath', 'the/directory/the_pattern_123.csv');

        $multiFileReader->read();
    }

    public function testReadNextItem()
    {
        $this->stepExecutionMock->expects($this->any())
            ->method('getJobParameters')
            ->willReturn($this->jobParametersMock);

        $this->jobParametersMock->expects($this->once())
            ->method('get');

        $this->finderMock->expects($this->once())
            ->method('in')
            ->willReturnSelf();

        $this->finderMock->expects($this->once())
            ->method('name')
            ->willReturnSelf();

        $this->finderMock->expects($this->once())
            ->method('getIterator')
            ->willReturn($this->filesIteratorMock);

        $this->filesIteratorMock->expects($this->once())
            ->method('current')
            ->willReturn($this->splFileInfoMock);

        $this->fileReaderMock->expects($this->once())
            ->method('read')
            ->willReturn(['item']);

        $this->assertEquals(['item'], $this->multiFileReader->read());
    }

    public function testReadNextItemCallsItself()
    {
        $this->stepExecutionMock->expects($this->any())
            ->method('getJobParameters')
            ->willReturn($this->jobParametersMock);

        $this->jobParametersMock->expects($this->once())
            ->method('get');

        $this->finderMock->expects($this->once())
            ->method('in')
            ->willReturnSelf();

        $this->finderMock->expects($this->once())
            ->method('name')
            ->willReturnSelf();

        $this->finderMock->expects($this->once())
            ->method('getIterator')
            ->willReturn($this->filesIteratorMock);

        $this->filesIteratorMock->expects($this->exactly(2))
            ->method('current')
            ->willReturn($this->splFileInfoMock);

        $this->filesIteratorMock->expects($this->once())
            ->method('next');

        $this->filesIteratorMock->expects($this->once())
            ->method('valid')
            ->willReturn(true);

        $this->fileReaderMock->expects($this->exactly(2))
            ->method('read')
            ->will($this->onConsecutiveCalls(null, ['item']));

        $this->assertEquals(['item'], $this->multiFileReader->read());
    }
}

interface DummyItemReaderStepExecutionAwareInitializableInterface
    extends ItemReaderInterface, StepExecutionAwareInterface, InitializableInterface {}