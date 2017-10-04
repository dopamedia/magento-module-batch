<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 04.10.17
 */

namespace Dopamedia\Batch\Test\Unit\Job\JobParameters;

use Dopamedia\Batch\Job\JobParameters\DefaultValuesProviderRegistry;
use Dopamedia\PhpBatch\Job\JobParameters\DefaultValuesProviderInterface;
use Dopamedia\PhpBatch\Job\JobParameters\NonExistingDefaultValuesProviderException;
use Dopamedia\PhpBatch\JobInterface;
use PHPUnit\Framework\TestCase;

class DefaultValuesProviderRegistryTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobInterface
     */
    protected $jobMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DefaultValuesProviderInterface
     */
    protected $defaultValuesProviderMock;

    protected function setUp()
    {
        $this->jobMock = $this->createMock(JobInterface::class);
        $this->defaultValuesProviderMock = $this->createMock(DefaultValuesProviderInterface::class);
    }

    public function testGetThrowsException()
    {
        $this->defaultValuesProviderMock->expects($this->once())
            ->method('supports')
            ->with($this->jobMock)
            ->willReturn(false);

        $this->jobMock->expects($this->once())
            ->method('getName')
            ->willReturn('Job Name');

        $defaultValuesProviderRegistry = new DefaultValuesProviderRegistry([$this->defaultValuesProviderMock]);
        $this->expectException(NonExistingDefaultValuesProviderException::class);
        $this->expectExceptionMessage('No default values provider has been defined for the Job "Job Name"');

        $defaultValuesProviderRegistry->get($this->jobMock);
    }

    public function testGet()
    {
        $this->defaultValuesProviderMock->expects($this->once())
            ->method('supports')
            ->with($this->jobMock)
            ->willReturn(true);

        $defaultValuesProviderRegistry = new DefaultValuesProviderRegistry([$this->defaultValuesProviderMock]);

        $this->assertSame(
            $this->defaultValuesProviderMock,
            $defaultValuesProviderRegistry->get($this->jobMock)
        );
    }
}
