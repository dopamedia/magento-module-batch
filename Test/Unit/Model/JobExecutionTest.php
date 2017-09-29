<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 27.09.17
 */

namespace Dopamedia\Batch\Test\Unit\Model;

use Dopamedia\Batch\Api\JobInstanceRepositoryInterface;
use Dopamedia\Batch\Model\JobExecution;
use Dopamedia\Batch\Model\StepExecution;
use Dopamedia\Batch\Model\ResourceModel\StepExecution\CollectionFactory as StepExecutionCollectionFactory;
use Dopamedia\Batch\Model\ResourceModel\StepExecution\Collection as StepExecutionCollection;
use Dopamedia\Batch\Model\StepExecutionFactory;
use Dopamedia\PhpBatch\BatchStatus;
use Dopamedia\PhpBatch\ExitStatus;
use Dopamedia\PhpBatch\JobInstanceInterface;
use Dopamedia\PhpBatch\Job\RuntimeErrorException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class JobExecutionTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobInstanceRepositoryInterface
     */
    protected $jobInstanceRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobInstanceInterface
     */
    protected $jobInstanceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Serializer
     */
    protected $serializerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StepExecutionFactory
     */
    protected $stepExecutionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StepExecution
     */
    protected $stepExecutionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StepExecutionCollectionFactory
     */
    protected $stepExecutionCollectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StepExecutionCollection
     */
    protected $stepExecutionCollectionMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->jobInstanceRepositoryMock = $this->createMock(JobInstanceRepositoryInterface::class);
        $this->jobInstanceMock = $this->createMock(JobInstanceInterface::class);
        $this->serializerMock = $this->createMock(Serializer::class);
        $this->stepExecutionFactoryMock = $this->getMockBuilder(StepExecutionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->stepExecutionMock = $this->createMock(StepExecution::class);

        $this->stepExecutionCollectionFactoryMock = $this->getMockBuilder(StepExecutionCollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->stepExecutionCollectionMock = $this->createMock(StepExecutionCollection::class);

        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @return object|JobExecution
     */
    protected function getJobExecutionObject()
    {
        return $this->objectManager->getObject(
            JobExecution::class,
            [
                'jobInstanceRepository' => $this->jobInstanceRepositoryMock,
                'serializer' => $this->serializerMock,
                'stepExecutionFactory' => $this->stepExecutionFactoryMock,
                'stepExecutionCollectionFactory' => $this->stepExecutionCollectionFactoryMock
            ]
        );
    }

    public function testGetJobInstanceWithoutJobInstanceId()
    {
        $jobExecution = $this->getJobExecutionObject();
        $jobExecution->setData('job_instance_id', null);

        $this->assertNull($jobExecution->getJobInstance());
    }

    /**
     * @group current
     */
    public function testGetJobInstanceWithNoSuchEntityException()
    {
        $this->jobInstanceRepositoryMock->expects($this->once())
            ->method('getById')
            ->with(11)
            ->willThrowException(new NoSuchEntityException(new Phrase('')));

        $jobExecution = $this->getJobExecutionObject();
        $jobExecution->setData('job_instance_id', 11);

        $this->expectException(NoSuchEntityException::class);

        $jobExecution->getJobInstance();

    }

    public function testGetJobInstance()
    {
        $this->jobInstanceRepositoryMock->expects($this->once())
            ->method('getById')
            ->with(11)
            ->willReturn($this->jobInstanceMock);

        $jobExecution = $this->getJobExecutionObject();
        $jobExecution->setData('job_instance_id', 11);

        $this->assertSame($this->jobInstanceMock, $jobExecution->getJobInstance());
    }

    public function testSetJobInstance()
    {
        $this->jobInstanceMock->expects($this->once())
            ->method('getId')
            ->willReturn(11);

        $jobExecution = $this->getJobExecutionObject();
        $jobExecution->setJobInstance($this->jobInstanceMock);

        $this->assertEquals(11, $jobExecution->getJobInstanceId());
    }

    public function testCreateStepExecution()
    {
        $jobExecution = $this->getJobExecutionObject();

        $this->stepExecutionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->stepExecutionMock);

        $this->stepExecutionMock->expects($this->once())
            ->method('setStepName')
            ->with('name')
            ->willReturnSelf();

        $this->stepExecutionMock->expects($this->once())
            ->method('setJobExecution')
            ->with($jobExecution)
            ->willReturnSelf();

        $jobExecution->createStepExecution('name');
    }

    public function testAddStepExecution()
    {
        $this->stepExecutionCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->stepExecutionCollectionMock);

        $this->stepExecutionCollectionMock->expects($this->once())
            ->method('setJobExecutionFilter')
            ->willReturnSelf();

        $this->stepExecutionCollectionMock->expects($this->once())
            ->method('addItem')
            ->with($this->stepExecutionMock);

        $this->getJobExecutionObject()->addStepExecution($this->stepExecutionMock);
    }

    public function testGetStepExecutions()
    {
        $jobExecution = $this->getJobExecutionObject();

        $this->stepExecutionCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->stepExecutionCollectionMock);

        $this->stepExecutionCollectionMock->expects($this->once())
            ->method('setJobExecutionFilter')
            ->with($jobExecution)
            ->willReturnSelf();

        $this->stepExecutionCollectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([]);

        $jobExecution->getStepExecutions();
    }

    public function testUpgradeStatus()
    {
        $status = BatchStatus::COMPLETED();

        $jobExecution = $this->getJobExecutionObject();

        $jobExecution->upgradeStatus($status);

        $this->assertNotSame($status, $jobExecution->getStatus());
    }

    public function testGetStatus()
    {
        $jobExecution = $this->getJobExecutionObject();
        $this->assertEquals(BatchStatus::STARTING, $jobExecution->getStatus()->getValue());

        $jobExecution->setData('status', BatchStatus::FAILED);
        $this->assertEquals(BatchStatus::FAILED, $jobExecution->getStatus()->getValue());

        $jobExecution->setData('status', null);
        $this->assertEquals(BatchStatus::STARTING, $jobExecution->getStatus()->getValue());
    }

    public function testGetExitStatus()
    {
        $jobExecution = $this->getJobExecutionObject();

        $this->assertEquals(ExitStatus::UNKNOWN, $jobExecution->getExitStatus()->getExitCode());
        $this->assertEmpty($jobExecution->getExitStatus()->getExitDescription());
    }

    public function testSetExitStatus()
    {
        $jobExecution = $this->getJobExecutionObject();
        $exitStatus = new ExitStatus(ExitStatus::FAILED, 'the description');
        $jobExecution->setExitStatus($exitStatus);

        $this->assertSame($exitStatus, $jobExecution->getExitStatus());
        $this->assertEquals(ExitStatus::FAILED, $jobExecution->getExitCode());
        $this->assertEquals('the description', $jobExecution->getExitDescription());
    }

    public function testGetExecutionContext()
    {
        $jobExecution = $this->getJobExecutionObject();
        $executionContext = $jobExecution->getExecutionContext();
        $this->assertSame($executionContext, $jobExecution->getExecutionContext());
    }

    public function testAddFailureException()
    {
        $jobExecution = $this->getJobExecutionObject();
        $jobExecution->setData('failure_exceptions', null);

        $exception = new \Exception('a message', 123);

        $jobExecution->addFailureException($exception);

        $failureException = $jobExecution->getFailureExceptions()[0];

        $this->assertCount(1, $jobExecution->getFailureExceptions());
        $this->assertEquals('Exception', $failureException['class']);
        $this->assertEquals('a message', $failureException['message']);
        $this->assertEmpty($failureException['messageParameters']);
        $this->assertEquals(123, $failureException['code']);

        $jobExecution = $this->getJobExecutionObject();
        $jobExecution->setData('failure_exceptions', null);

        $exception = new RuntimeErrorException('a message', ['first', 'second']);

        $jobExecution->addFailureException($exception);

        $failureException = $jobExecution->getFailureExceptions()[0];

        $this->assertCount(1, $jobExecution->getFailureExceptions());
        $this->assertEquals(RuntimeErrorException::class, $failureException['class']);
        $this->assertEquals('a message', $failureException['message']);
        $this->assertEquals(['first', 'second'], $failureException['messageParameters']);

        $jobExecution = $this->getJobExecutionObject();
        $jobExecution->setData('failure_exceptions', null);

        for ($i = 0; $i < 3; $i++) {
            $jobExecution->addFailureException(new \Exception());
        }

        $this->assertcount(3, $jobExecution->getFailureExceptions());
    }

    public function testGetFailureExceptions()
    {
        $jobExecution = $this->getJobExecutionObject();
        $jobExecution->setData('failure_exceptions', null);

        $this->assertEquals([], $jobExecution->getFailureExceptions());

        $jobExecution->setData('failure_exceptions', '{"key":"value"}');

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('{"key":"value"}')
            ->willReturn(['key' => 'value']);

        $this->assertEquals(['key' => 'value'], $jobExecution->getFailureExceptions());

        $jobExecution->setData('failure_exceptions', ['key' => 'value']);

        $this->assertEquals(['key' => 'value'], $jobExecution->getFailureExceptions());

        $jobExecution->setData('failure_exceptions', 11);

        $this->assertEquals([], $jobExecution->getFailureExceptions());
    }

    public function testGetAllFailureExecptions()
    {
        $jobExecution = $this->getJobExecutionObject();

        $this->stepExecutionCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->stepExecutionCollectionMock);

        $this->stepExecutionCollectionMock->expects($this->once())
            ->method('setJobExecutionFilter')
            ->willReturnSelf();

        $this->stepExecutionMock->expects($this->once())
            ->method('getFailureExceptions')
            ->willReturn(['second', 'third']);

        $this->stepExecutionCollectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->stepExecutionMock]);

        $jobExecution->setData('failure_exceptions', ['first']);

        $this->assertEquals(['first', 'second', 'third'], $jobExecution->getAllFailureExceptions());
    }

    public function testIsStopping()
    {
        $jobExecution = $this->getJobExecutionObject();
        $jobExecution->setStatus(BatchStatus::STARTED());

        $this->assertFalse($jobExecution->isStopping());

        $jobExecution->setStatus(BatchStatus::STOPPING());

        $this->assertTrue($jobExecution->isStopping());
    }

    public function testBeforeSaveStatus()
    {
        $jobExecution = $this->getJobExecutionObject();
        $jobExecution->setData('status', BatchStatus::STARTED());

        $jobExecution->beforeSave();

        $this->assertEquals(
            BatchStatus::STARTED,
            $jobExecution->getData('status')
        );
    }

    public function testBeforeSaveFailureExceptionsWithoutValues()
    {
        $jobExecution = $this->getJobExecutionObject();
        $jobExecution->setData('failure_exceptions', null);

        $this->serializerMock->expects($this->never())
            ->method('serialize');

        $jobExecution->beforeSave();
    }

    public function testBeforeSaveFailureExceptions()
    {
        $jobExecution = $this->getJobExecutionObject();
        $jobExecution->setData('failure_exceptions', [['key' => 'value']]);

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with([['key' => 'value']])
            ->willReturn('[{"key":"value"}]');

        $jobExecution->beforeSave();

        $this->assertEquals(
            '[{"key":"value"}]',
            $jobExecution->getData('failure_exceptions')
        );
    }

}
