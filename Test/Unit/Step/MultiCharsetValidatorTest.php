<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 16.10.17
 */

namespace Dopamedia\Batch\Test\Unit\Step;

use Dopamedia\Batch\Item\CharsetValidator;
use Dopamedia\Batch\Item\MultiCharsetValidator;
use Dopamedia\PhpBatch\Job\JobParameters;
use Dopamedia\PhpBatch\StepExecutionInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class MultiCharsetValidatorTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CharsetValidator
     */
    protected $charsetValidatorMock;

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
     * @var MultiCharsetValidator
     */
    protected $multiCharsetValidator;

    protected function setUp()
    {
        $this->charsetValidatorMock = $this->createMock(CharsetValidator::class);
        $this->finderMock = $this->createMock(Finder::class);
        $this->stepExecutionMock = $this->createMock(StepExecutionInterface::class);
        $this->jobParametersMock = $this->createMock(JobParameters::class);
        $this->multiCharsetValidator = new MultiCharsetValidator($this->charsetValidatorMock, $this->finderMock);
        $this->multiCharsetValidator->setStepExecution($this->stepExecutionMock);
    }
    
    public function testValidate()
    {
        $this->stepExecutionMock->expects($this->exactly(3))
            ->method('getJobParameters')
            ->willReturn($this->jobParametersMock);

        $this->jobParametersMock->expects($this->once())
            ->method('get')
            ->with('filePattern')
            ->willReturn('/the/file/pattern_*.csv');

        $this->finderMock->expects($this->once())
            ->method('in')
            ->with('/the/file')
            ->willReturnSelf();

        $this->finderMock->expects($this->once())
            ->method('name')
            ->with('pattern_*.csv')
            ->willReturnSelf();


        /** @var \PHPUnit_Framework_MockObject_MockObject|SplFileInfo $firstFileMock */
        $firstFileMock = $this->createMock(SplFileInfo::class);

        $firstFileMock->expects($this->once())
            ->method('getRealPath')
            ->willReturn('/the/file/pattern_first.csv');


        /** @var \PHPUnit_Framework_MockObject_MockObject|SplFileInfo $secondFileMock */
        $secondFileMock = $this->createMock(SplFileInfo::class);

        $secondFileMock->expects($this->once())
            ->method('getRealPath')
            ->willReturn('/the/file/pattern_second.csv');

        $filesIterator = new \ArrayIterator();

        $filesIterator->append($firstFileMock);
        $filesIterator->append($secondFileMock);

        $this->finderMock->expects($this->once())
            ->method('getIterator')
            ->willReturn($filesIterator);

        $this->jobParametersMock->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(
                ['filePath', '/the/file/pattern_first.csv'],
                ['filePath', '/the/file/pattern_second.csv']
            );

        $this->charsetValidatorMock->expects($this->exactly(2))
            ->method('setStepExecution')
            ->with($this->stepExecutionMock);

        $this->charsetValidatorMock->expects($this->exactly(2))
            ->method('validate');

        $this->multiCharsetValidator->validate();
    }
}
