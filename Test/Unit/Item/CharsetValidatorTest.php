<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 02.10.17
 */

namespace Dopamedia\Batch\Test\Unit\Item;

use Dopamedia\Batch\Item\CharsetValidator;
use Dopamedia\PhpBatch\Job\JobParameters;
use Dopamedia\PhpBatch\StepExecutionInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class CharsetValidatorTest extends TestCase
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
     * @var string
     */
    protected $filePath;

    /**
     * @var CharsetValidator
     */
    protected $charsetValidator;

    protected function setUp()
    {
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->stepExecutionMock = $this->createMock(StepExecutionInterface::class);
        $this->jobParametersMock = $this->createMock(JobParameters::class);
        $this->stepExecutionMock->expects($this->any())
            ->method('getJobParameters')
            ->willReturn($this->jobParametersMock);

        $this->filePath = realpath(__DIR__) . '/CharsetValidatorTest/_files/';

        $this->charsetValidator = new CharsetValidator($this->filesystemMock);
        $this->charsetValidator->setStepExecution($this->stepExecutionMock);
    }

    public function testValidateWithAbsentFile()
    {
        $this->filesystemMock->expects($this->once())
            ->method('exists')
            ->with('absent/file.csv')
            ->willReturn(false);

        $this->jobParametersMock->expects($this->once())
            ->method('get')
            ->with('filePath')
            ->willReturn('absent/file.csv');

        $this->expectException(FileSystemException::class);

        $this->charsetValidator->validate();
    }

    public function testValidateInvalidCharset()
    {
        $this->jobParametersMock->expects($this->once())
            ->method('get')
            ->with('filePath')
            ->willReturn($this->filePath . '/ISO-8859-1.csv');

        $this->expectException(LocalizedException::class);

        $this->charsetValidator->validate();
    }


    public function testValidate()
    {
        $this->jobParametersMock->expects($this->once())
            ->method('get')
            ->with('filePath')
            ->willReturn($this->filePath . '/UTF-8.csv');

        $this->stepExecutionMock->expects($this->once())
            ->method('addSummaryInfo')
            ->with('Charset Validator', 'UTF-8 OK');

        $this->charsetValidator->validate();

    }
}
