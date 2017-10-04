<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 04.10.17
 */

namespace Dopamedia\Batch\Test\Unit\Job\JobParameters;

use Dopamedia\Batch\Job\JobParameters\ValidatorProviderRegistry;
use Dopamedia\PhpBatch\Job\JobParameters\NonExistingValidatorProviderException;
use Dopamedia\PhpBatch\Job\JobParameters\ValidatorProviderInterface;
use Dopamedia\PhpBatch\JobInterface;
use PHPUnit\Framework\TestCase;

class ValidatorProviderRegistryTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobInterface
     */
    protected $jobMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ValidatorProviderInterface
     */
    protected $validatorProviderMock;

    protected function setUp()
    {
        $this->jobMock = $this->createMock(JobInterface::class);
        $this->validatorProviderMock = $this->createMock(ValidatorProviderInterface::class);
    }

    public function testGetThrowsException()
    {
        $this->validatorProviderMock->expects($this->once())
            ->method('supports')
            ->with($this->jobMock)
            ->willReturn(false);

        $this->jobMock->expects($this->once())
            ->method('getName')
            ->willReturn('Job Name');

        $validatorProviderRegistry = new ValidatorProviderRegistry([$this->validatorProviderMock]);

        $this->expectException(NonExistingValidatorProviderException::class);
        $this->expectExceptionMessage('No validator provider has been defined for the Job "Job Name"');

        $validatorProviderRegistry->get($this->jobMock);
    }

    public function testGet()
    {
        $this->validatorProviderMock->expects($this->once())
            ->method('supports')
            ->with($this->jobMock)
            ->willReturn(true);

        $validatorProviderRegistry = new ValidatorProviderRegistry([$this->validatorProviderMock]);

        $this->assertSame($this->validatorProviderMock, $validatorProviderRegistry->get($this->jobMock));

    }


}
