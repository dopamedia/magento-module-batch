<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 08.10.17
 */

namespace Dopamedia\Batch\Test\Unit\Model;

use Dopamedia\Batch\Model\JobExecution;
use Dopamedia\Batch\Model\JobExecutionFactory;
use Dopamedia\Batch\Model\JobInstance;
use Dopamedia\Batch\Model\JobInstanceFactory;
use Dopamedia\Batch\Model\JobRepository;
use Dopamedia\Batch\Model\ResourceModel\JobInstance\Collection as JobInstanceCollection;
use Dopamedia\Batch\Model\StepExecution;
use Dopamedia\Batch\Model\StepExecutionFactory;
use Dopamedia\Batch\Model\Warning;
use Dopamedia\Batch\Model\WarningFactory;
use Dopamedia\Batch\Model\ResourceModel\JobExecution as ResourceJobExecution;
use Dopamedia\Batch\Model\ResourceModel\JobInstance as ResourceJobInstance;
use Dopamedia\Batch\Model\ResourceModel\StepExecution as ResourceStepExecution;
use Dopamedia\Batch\Model\ResourceModel\Warning as ResourceWarning;
use Dopamedia\PhpBatch\Job\JobParameters;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;

class JobRepositoryTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ResourceJobExecution
     */
    protected $resourceJobExecutionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobExecutionFactory
     */
    protected $jobExecutionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobExecution
     */
    protected $jobExecutionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ResourceJobInstance
     */
    protected $resourceJobInstanceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobInstanceFactory
     */
    protected $jobInstanceFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobInstance
     */
    protected $jobInstanceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobInstanceCollection
     */
    protected $jobInstanceCollectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ResourceStepExecution
     */
    protected $resourceStepExecutionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StepExecutionFactory
     */
    protected $stepExecutionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StepExecution
     */
    protected $stepExecutionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ResourceWarning
     */
    protected $resourceWarningMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|WarningFactory
     */
    protected $warningFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Warning
     */
    protected $warningMock;

    /**
     * @var JobRepository
     */
    protected $jobRepository;

    protected function setUp()
    {
        $this->resourceJobExecutionMock = $this->createMock(ResourceJobExecution::class);

        $this->jobExecutionFactoryMock = $this->getMockBuilder(JobExecutionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->jobExecutionMock = $this->createMock(JobExecution::class);

        $this->resourceJobInstanceMock = $this->createMock(ResourceJobInstance::class);

        $this->jobInstanceFactoryMock = $this->createMock(JobInstanceFactory::class);

        $this->jobInstanceMock = $this->createMock(JobInstance::class);

        $this->jobInstanceCollectionMock = $this->createMock(JobInstanceCollection::class);

        $this->resourceStepExecutionMock = $this->createMock(ResourceStepExecution::class);

        $this->stepExecutionFactoryMock = $this->getMockBuilder(StepExecutionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->stepExecutionMock = $this->createMock(StepExecution::class);

        $this->resourceWarningMock = $this->createMock(ResourceWarning::class);

        $this->warningFactoryMock = $this->getMockBuilder(WarningFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->warningMock = $this->createMock(Warning::class);

        $this->jobRepository = new JobRepository(
            $this->resourceJobExecutionMock,
            $this->jobExecutionFactoryMock,
            $this->resourceJobInstanceMock,
            $this->jobInstanceFactoryMock,
            $this->jobInstanceCollectionMock,
            $this->resourceStepExecutionMock,
            $this->stepExecutionFactoryMock,
            $this->resourceWarningMock,
            $this->warningFactoryMock
        );
    }

    public function testGetJobExecutionByIdThrowsException()
    {
        $this->jobExecutionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->jobExecutionMock);

        $this->resourceJobExecutionMock->expects($this->once())
            ->method('load')
            ->with($this->jobExecutionMock, 123);

        $this->jobExecutionMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('JobExecution with id "123" does not exist');

        $this->jobRepository->getJobExecutionById(123);
    }

    public function testGetJobExecutionById()
    {
        $this->jobExecutionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->jobExecutionMock);

        $this->resourceJobExecutionMock->expects($this->once())
            ->method('load')
            ->with($this->jobExecutionMock, 123);

        $this->jobExecutionMock->expects($this->once())
            ->method('getId')
            ->willReturn(123);

        $this->jobRepository->getJobExecutionById(123);
    }

    public function testCreateJobExecution()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|JobParameters $jobParametersMock */
        $jobParametersMock = $this->createMock(JobParameters::class);

        $this->resourceJobInstanceMock->expects($this->once())
            ->method('save')
            ->with($this->jobInstanceMock);

        $this->jobExecutionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->jobExecutionMock);

        $this->jobExecutionMock->expects($this->once())
            ->method('setJobInstance')
            ->with($this->jobInstanceMock)
            ->willReturnSelf();

        $this->jobExecutionMock->expects($this->once())
            ->method('setJobParameters')
            ->with($jobParametersMock)
            ->willReturnSelf();

        $this->resourceJobExecutionMock->expects($this->once())
            ->method('save')
            ->with($this->jobExecutionMock);

        $this->jobRepository->createJobExecution($this->jobInstanceMock, $jobParametersMock);
    }

    public function testSaveJobExecutionThrowsException()
    {
        $this->resourceJobExecutionMock->expects($this->once())
            ->method('save')
            ->with($this->jobExecutionMock)
            ->willThrowException(new \Exception('exception message'));

        $this->expectException(CouldNotSaveException::class);
        $this->expectExceptionMessage('exception message');

        $this->jobRepository->saveJobExecution($this->jobExecutionMock);
    }

    public function testSaveJobExecution()
    {
        $this->resourceJobExecutionMock->expects($this->once())
            ->method('save')
            ->with($this->jobExecutionMock);

        $this->assertSame(
            $this->jobExecutionMock,
            $this->jobRepository->saveJobExecution($this->jobExecutionMock)
        );
    }

    public function testGetJobInstanceByIdThrowsException()
    {
        $this->jobInstanceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->jobInstanceMock);

        $this->resourceJobInstanceMock->expects($this->once())
            ->method('load')
            ->with($this->jobInstanceMock, 123);

        $this->jobInstanceMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('JobInstance with id "123" does not exist.');

        $this->jobRepository->getJobInstanceById(123);
    }

    public function testGetJobInstanceById()
    {
        $this->jobInstanceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->jobInstanceMock);

        $this->resourceJobInstanceMock->expects($this->once())
            ->method('load')
            ->with($this->jobInstanceMock, 123);

        $this->jobInstanceMock->expects($this->once())
            ->method('getId')
            ->willReturn(123);

        $this->assertSame(
            $this->jobInstanceMock,
            $this->jobRepository->getJobInstanceById(123)
        );
    }

    public function testGetJobInstanceByCodeThrowsException()
    {
        $this->jobInstanceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->jobInstanceMock);

        $this->resourceJobInstanceMock->expects($this->once())
            ->method('load')
            ->with($this->jobInstanceMock, 'code', JobInstance::CODE);

        $this->jobInstanceMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('JobInstance with code "code" does not exist.');

        $this->jobRepository->getJobInstanceByCode('code');
    }

    public function testGetJobInstanceByCode()
    {
        $this->jobInstanceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->jobInstanceMock);

        $this->resourceJobInstanceMock->expects($this->once())
            ->method('load')
            ->with($this->jobInstanceMock, 'code', JobInstance::CODE);

        $this->jobInstanceMock->expects($this->once())
            ->method('getId')
            ->willReturn(123);

        $this->assertSame(
            $this->jobInstanceMock,
            $this->jobRepository->getJobInstanceByCode('code')
        );
    }

    public function testGetJobInstances()
    {
        $this->jobInstanceCollectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([]);

        $this->jobRepository->getJobInstances();
    }

    public function testSaveJobInstanceThrowsException()
    {
        $this->resourceJobInstanceMock->expects($this->once())
            ->method('save')
            ->with($this->jobInstanceMock)
            ->willThrowException(new \Exception('exception message'));

        $this->expectException(CouldNotSaveException::class);
        $this->expectExceptionMessage('exception message');

        $this->jobRepository->saveJobInstance($this->jobInstanceMock);
    }

    public function testSaveJobInstance()
    {
        $this->resourceJobInstanceMock->expects($this->once())
            ->method('save')
            ->with($this->jobInstanceMock);

        $this->assertSame(
            $this->jobInstanceMock,
            $this->jobRepository->saveJobInstance($this->jobInstanceMock)
        );
    }

    public function testGetStepExecutionByIdThrowsException()
    {

        $this->stepExecutionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->stepExecutionMock);


        $this->resourceStepExecutionMock->expects($this->once())
            ->method('load')
            ->with($this->stepExecutionMock, 123);

        $this->stepExecutionMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('StepExecution with id "123" does not exist.');

        $this->jobRepository->getStepExecutionById(123);
    }

    public function testGetStepExecutionById()
    {
        $this->stepExecutionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->stepExecutionMock);

        $this->resourceStepExecutionMock->expects($this->once())
            ->method('load')
            ->with($this->stepExecutionMock, 123);

        $this->stepExecutionMock->expects($this->once())
            ->method('getId')
            ->willReturn(123);

        $this->assertSame(
            $this->stepExecutionMock,
            $this->jobRepository->getStepExecutionById(123)
        );
    }

    public function testSaveStepExecutionThrowsException()
    {
        $this->resourceStepExecutionMock->expects($this->once())
            ->method('save')
            ->with($this->stepExecutionMock)
            ->willThrowException(new \Exception('exception message'));

        $this->expectException(CouldNotSaveException::class);
        $this->expectExceptionMessage('exception message');

        $this->jobRepository->saveStepExecution($this->stepExecutionMock);
    }

    public function testSaveStepExecution()
    {
        $this->resourceStepExecutionMock->expects($this->once())
            ->method('save')
            ->with($this->stepExecutionMock);

        $this->assertSame(
            $this->stepExecutionMock,
            $this->jobRepository->saveStepExecution($this->stepExecutionMock)
        );
    }

    public function testGetWarningByIdThrowsException()
    {
        $this->warningFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->warningMock);

        $this->resourceWarningMock->expects($this->once())
            ->method('load')
            ->with($this->warningMock, 123);

        $this->warningMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('Warning with id "123" does not exist.');

        $this->jobRepository->getWarningById(123);
    }

    public function testGetWarningById()
    {
        $this->warningFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->warningMock);

        $this->resourceWarningMock->expects($this->once())
            ->method('load')
            ->with($this->warningMock, 123);

        $this->warningMock->expects($this->once())
            ->method('getId')
            ->willReturn(123);

        $this->assertSame(
            $this->warningMock,
            $this->jobRepository->getWarningById(123)
        );
    }

    public function testCreateWarning()
    {
        $this->warningFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->warningMock);

        $this->warningMock->expects($this->once())
            ->method('setStepExecution')
            ->with($this->stepExecutionMock)
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

        $this->resourceWarningMock->expects($this->once())
            ->method('save');

        $this->assertSame(
            $this->warningMock,
            $this->jobRepository->createWarning(
                $this->stepExecutionMock,
                'reason',
                ['param1', 'param2'],
                ['item']
            )
        );
    }

    public function testSaveWarningThrowsException()
    {
        $this->resourceWarningMock->expects($this->once())
            ->method('save')
            ->with($this->warningMock)
            ->willThrowException(new \Exception('exception message'));

        $this->expectException(CouldNotSaveException::class);
        $this->expectExceptionMessage('exception message');

        $this->jobRepository->saveWarning($this->warningMock);
    }

    public function testSaveWarning()
    {
        $this->resourceWarningMock->expects($this->once())
            ->method('save')
            ->with($this->warningMock);

        $this->assertSame(
            $this->warningMock,
            $this->jobRepository->saveWarning($this->warningMock)
        );
    }
}
