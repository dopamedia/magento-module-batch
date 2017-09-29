<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 29.09.17
 */

namespace Dopamedia\Batch\Test\Unit\Model;

use Dopamedia\Batch\Api\JobExecutionRepositoryInterface;
use Dopamedia\Batch\Api\JobInstanceRepositoryInterface;
use Dopamedia\Batch\Api\StepExecutionRepositoryInterface;
use Dopamedia\Batch\Api\WarningRepositoryInterface;
use Dopamedia\Batch\Model\JobRepository;
use Dopamedia\PhpBatch\Job\JobParameters;
use Dopamedia\PhpBatch\JobExecutionInterface;
use Dopamedia\PhpBatch\JobExecutionInterfaceFactory;
use Dopamedia\PhpBatch\JobInstanceInterface;
use Dopamedia\PhpBatch\StepExecutionInterface;
use Dopamedia\PhpBatch\WarningInterface;
use Dopamedia\PhpBatch\WarningInterfaceFactory;
use PHPUnit\Framework\TestCase;

class JobRepositoryTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobExecutionRepositoryInterface
     */
    protected $jobExecutionRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobInstanceRepositoryInterface
     */
    protected $jobInstanceRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StepExecutionRepositoryInterface
     */
    protected $stepExecutionRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|WarningRepositoryInterface
     */
    protected $warningRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobExecutionInterfaceFactory
     */
    protected $jobExecutionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobExecutionInterface
     */
    protected $jobExecutionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|WarningInterfaceFactory
     */
    protected $warningFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|WarningInterface
     */
    protected $warningMock;

    /**
     * @var JobRepository
     */
    protected $jobRepository;

    protected function setUp()
    {
        $this->jobExecutionRepositoryMock = $this->createMock(JobExecutionRepositoryInterface::class);

        $this->jobInstanceRepositoryMock = $this->createMock(JobInstanceRepositoryInterface::class);

        $this->stepExecutionRepositoryMock = $this->createMock(StepExecutionRepositoryInterface::class);

        $this->warningRepositoryMock = $this->createMock(WarningRepositoryInterface::class);

        $this->jobExecutionFactoryMock = $this->getMockBuilder(JobExecutionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->jobExecutionMock = $this->createMock(JobExecutionInterface::class);

        $this->warningFactoryMock = $this->getMockBuilder(WarningInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->warningMock = $this->createMock(WarningInterface::class);

        $this->jobRepository = new JobRepository(
            $this->jobExecutionRepositoryMock,
            $this->jobInstanceRepositoryMock,
            $this->stepExecutionRepositoryMock,
            $this->warningRepositoryMock,
            $this->jobExecutionFactoryMock,
            $this->warningFactoryMock
        );
    }

    public function testCreateJobExecution()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|JobInstanceInterface $jobInstanceMock */
        $jobInstanceMock = $this->createMock(JobInstanceInterface::class);

        /** @var \PHPUnit_Framework_MockObject_MockObject|JobParameters $jobParametersMock */
        $jobParametersMock = $this->createMock(JobParameters::class);

        $this->jobInstanceRepositoryMock->expects($this->once())
            ->method('save')
            ->with($jobInstanceMock);

        $this->jobExecutionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->jobExecutionMock);

        $this->jobExecutionMock->expects($this->once())
            ->method('setJobInstance')
            ->with($jobInstanceMock)
            ->willReturnSelf();

        $this->jobExecutionMock->expects($this->once())
            ->method('setJobParameters')
            ->with($jobParametersMock)
            ->willReturnSelf();

        $this->jobExecutionRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->jobExecutionMock);

        $this->assertSame(
            $this->jobExecutionMock,
            $this->jobRepository->createJobExecution($jobInstanceMock, $jobParametersMock)
        );
    }

    public function testUpdateJobExecution()
    {
        $this->jobExecutionRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->jobExecutionMock);

        $this->jobRepository->updateJobExecution($this->jobExecutionMock);
    }

    public function testUpdateStepExecution()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|StepExecutionInterface $stepExecutionMock */
        $stepExecutionMock = $this->createMock(StepExecutionInterface::class);

        $this->stepExecutionRepositoryMock->expects($this->once())
            ->method('save')
            ->with($stepExecutionMock);

        $this->jobRepository->updateStepExecution($stepExecutionMock);
    }


    public function testCreateWarning()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|StepExecutionInterface $stepExecutionMock */
        $stepExecutionMock = $this->createMock(StepExecutionInterface::class);

        $this->warningFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->warningMock);

        $this->warningMock->expects($this->once())
            ->method('setStepExecution')
            ->with($stepExecutionMock)
            ->willReturnSelf();

        $this->warningMock->expects($this->once())
            ->method('setReason')
            ->with('reason')
            ->willReturnSelf();

        $this->warningMock->expects($this->once())
            ->method('setReasonParameters')
            ->with(['param1', 'param2'])
            ->willReturnSelf();

        $this->warningMock->expects($this->once())
            ->method('setItem')
            ->with(['item'])
            ->willReturnSelf();

        $this->warningRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->warningMock)
            ->willReturn($this->warningMock);

        $this->assertSame(
            $this->warningMock,
            $this->jobRepository->createWarning(
                $stepExecutionMock,
                'reason',
                ['param1', 'param2'],
                ['item']
            )
        );
    }






}
