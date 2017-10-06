<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 28.09.17
 */

namespace Dopamedia\Batch\Test\Unit\Model;

use Dopamedia\Batch\Api\JobExecutionRepositoryInterface;
use Dopamedia\Batch\Model\StepExecution;
use Dopamedia\Batch\Model\Warning;
use Dopamedia\Batch\Model\WarningFactory;
use Dopamedia\PhpBatch\BatchStatus;
use Dopamedia\PhpBatch\ExitStatus;
use Dopamedia\PhpBatch\Item\InvalidItemInterface;
use Dopamedia\PhpBatch\Job\RuntimeErrorException;
use Dopamedia\PhpBatch\JobExecutionInterface;
use Dopamedia\PhpBatch\Job\JobParameters;
use Dopamedia\PhpBatch\Repository\JobRepositoryInterface;
use Dopamedia\PhpBatch\WarningInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Dopamedia\Batch\Model\ResourceModel\Warning\Collection as WarningCollection;
use Dopamedia\Batch\Model\ResourceModel\Warning\CollectionFactory as WarningCollectionFactory;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @group current
 */
class StepExecutionTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobRepositoryInterface
     */
    protected $jobRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobExecutionInterface
     */
    protected $jobExecutionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobParameters
     */
    protected $jobParametersMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Serializer
     */
    protected $serializerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|WarningCollectionFactory
     */
    protected $warningCollectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|WarningCollection
     */
    protected $warningCollectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|WarningFactory
     */
    protected $warningFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Warning
     */
    protected $warningMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;


    protected function setUp()
    {
        $this->jobRepository = $this->createMock(JobRepositoryInterface::class);
        $this->jobExecutionMock = $this->createMock(JobExecutionInterface::class);
        $this->jobParametersMock = $this->createMock(JobParameters::class);
        $this->serializerMock = $this->createMock(Serializer::class);
        $this->warningCollectionFactoryMock = $this->getMockBuilder(WarningCollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->warningCollectionMock = $this->createMock(WarningCollection::class);
        $this->warningFactoryMock = $this->getMockBuilder(WarningFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->warningMock = $this->createMock(Warning::class);
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @return object|StepExecution
     */
    protected function getStepExecutionObject()
    {
        return $this->objectManager->getObject(
            StepExecution::class,
            [
                'jobRepository' => $this->jobRepository,
                'serializer' => $this->serializerMock,
                'warningCollectionFactory' => $this->warningCollectionFactoryMock,
                'warningFactory' => $this->warningFactoryMock
            ]
        );
    }

    public function testGetJobExecutionThrowsLocalizedException()
    {
        $this->expectException(LocalizedException::class);

        $this->jobRepository->expects($this->once())
            ->method('getJobExecutionById')
            ->willThrowException(new LocalizedException(new Phrase('')));

        $stepExecution = $this->getStepExecutionObject();
        $stepExecution->setJobExecutionId(123);

        $stepExecution->getJobExecution();
    }

    public function testGetJobExecution()
    {
        $this->jobRepository->expects($this->once())
            ->method('getJobExecutionById')
            ->with(123)
            ->willReturn($this->jobExecutionMock);

        $stepExecution = $this->getStepExecutionObject();
        $stepExecution->setJobExecutionId(123);

        $this->assertSame($this->jobExecutionMock, $stepExecution->getJobExecution());
    }

    public function testSetJobExecution()
    {
        $this->jobExecutionMock->expects($this->once())
            ->method('getId')
            ->willReturn(123);

        $stepExecution = $this->getStepExecutionObject();

        $stepExecution->setJobExecution($this->jobExecutionMock);

        $this->assertEquals(123, $stepExecution->getJobExecutionId());
        $this->assertSame($this->jobExecutionMock, $stepExecution->getJobExecution());
    }

    public function testGetExecutionContext()
    {
        $stepExecution = $this->getStepExecutionObject();

        $executionContext = $stepExecution->getExecutionContext();

        $this->assertSame($executionContext, $stepExecution->getExecutionContext());
    }

    public function testGetStatus()
    {
        $stepExecution = $this->getStepExecutionObject();

        $status = BatchStatus::FAILED();
        $stepExecution->setStatus($status);
        $this->assertSame($status, $stepExecution->getStatus());

        $stepExecution->setData('status', BatchStatus::STOPPED);
        $this->assertEquals(BatchStatus::STOPPED, $stepExecution->getStatus()->getValue());

        $stepExecution->setData('status', null);
        $this->assertEquals(BatchStatus::STARTING, $stepExecution->getStatus()->getValue());
    }

    public function testUpgradeStatus()
    {
        $status = BatchStatus::STARTING();

        $stepExecution = $this->getStepExecutionObject();
        $stepExecution->upgradeStatus($status);

        $this->assertNotSame($status, $stepExecution->getStatus());
    }

    public function testSetExitStatus()
    {
        $exitStatus = new ExitStatus(ExitStatus::STOPPED, 'a description');

        $stepExecution = $this->getStepExecutionObject();

        $stepExecution->setExitStatus($exitStatus);

        $this->assertSame($exitStatus, $stepExecution->getExitStatus());
        $this->assertEquals(ExitStatus::STOPPED, $stepExecution->getData(StepExecution::EXIT_CODE));
        $this->assertEquals('a description', $stepExecution->getData(StepExecution::EXIT_DESCRIPTION));
    }

    public function testGetExitStatus()
    {
        $stepExecution = $this->getStepExecutionObject();

        $this->assertEquals(ExitStatus::EXECUTING, $stepExecution->getExitStatus()->getExitCode());
    }

    public function testGetJobParameters()
    {
        $stepExecution = $this->getStepExecutionObject();

        $this->jobExecutionMock->expects($this->once())
            ->method('getId')
            ->willReturn(123);

        $this->jobExecutionMock->expects($this->once())
            ->method('getJobParameters')
            ->willReturn($this->jobParametersMock);

        $stepExecution->setJobExecution($this->jobExecutionMock);

        $this->assertSame($this->jobParametersMock, $stepExecution->getJobParameters());
    }

    public function testGetFailureExceptions()
    {
        $stepExecution = $this->getStepExecutionObject();
        $stepExecution->setData('failure_exceptions', null);

        $this->assertEquals([], $stepExecution->getFailureExceptions());

        $stepExecution->setData('failure_exceptions', '{"key":"value"}');

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('{"key":"value"}')
            ->willReturn(['key' => 'value']);

        $this->assertEquals(['key' => 'value'], $stepExecution->getFailureExceptions());

        $stepExecution->setData('failure_exceptions', ['key' => 'value']);

        $this->assertEquals(['key' => 'value'], $stepExecution->getFailureExceptions());

        $stepExecution->setData('failure_exceptions', 11);

        $this->assertEquals([], $stepExecution->getFailureExceptions());
    }

    public function testAddFailureException()
    {
        $stepExecution = $this->getStepExecutionObject();
        $stepExecution->setData('failure_exceptions', null);

        $exception = new \Exception('a message', 123);

        $stepExecution->addFailureException($exception);

        $failureException = $stepExecution->getFailureExceptions()[0];

        $this->assertCount(1, $stepExecution->getFailureExceptions());
        $this->assertEquals('Exception', $failureException['class']);
        $this->assertEquals('a message', $failureException['message']);
        $this->assertEmpty($failureException['messageParameters']);
        $this->assertEquals(123, $failureException['code']);

        $stepExecution = $this->getStepExecutionObject();
        $stepExecution->setData('failure_exceptions', null);

        $exception = new RuntimeErrorException('a message', ['first', 'second']);

        $stepExecution->addFailureException($exception);

        $failureException = $stepExecution->getFailureExceptions()[0];

        $this->assertCount(1, $stepExecution->getFailureExceptions());
        $this->assertEquals(RuntimeErrorException::class, $failureException['class']);
        $this->assertEquals('a message', $failureException['message']);
        $this->assertEquals(['first', 'second'], $failureException['messageParameters']);

        $stepExecution = $this->getStepExecutionObject();
        $stepExecution->setData('failure_exceptions', null);

        for ($i = 0; $i < 3; $i++) {
            $stepExecution->addFailureException(new \Exception());
        }

        $this->assertcount(3, $stepExecution->getFailureExceptions());
    }

    public function testGetErrors()
    {
        $stepExecution = $this->getStepExecutionObject();
        $stepExecution->setData('errors', null);

        $this->assertEquals([], $stepExecution->getErrors());

        $stepExecution->setData('errors', '{"key":"value"}');

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('{"key":"value"}')
            ->willReturn(['key' => 'value']);

        $this->assertEquals(['key' => 'value'], $stepExecution->getErrors());

        $stepExecution->setData('errors', ['key' => 'value']);

        $this->assertEquals(['key' => 'value'], $stepExecution->getErrors());

        $stepExecution->setData('errors', 11);

        $this->assertEquals([], $stepExecution->getErrors());
    }

    public function testAddError()
    {
        $stepExecution = $this->getStepExecutionObject();
        $stepExecution->setData('errors', null);

        $stepExecution->addError('first_error');

        $this->assertEquals(['first_error'], $stepExecution->getData('errors'));

        $stepExecution->addError('second_error');

        $this->assertEquals(['first_error', 'second_error'], $stepExecution->getData('errors'));
    }

    public function testGetWarnings()
    {
        $stepExecution = $this->getStepExecutionObject();

        $this->warningCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->warningCollectionMock);

        $this->warningCollectionMock->expects($this->once())
            ->method('setStepExecutionFilter')
            ->with($stepExecution)
            ->willReturnSelf();

        $this->warningCollectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([]);

        $stepExecution->getWarnings();
    }

    public function testAddWarning()
    {
        $stepExecution = $this->getStepExecutionObject();

        $this->warningCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->warningCollectionMock);

        $this->warningCollectionMock->expects($this->once())
            ->method('setStepExecutionFilter')
            ->willReturnSelf();

        $this->warningFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->warningMock);

        $this->warningMock->expects($this->once())
            ->method('setReason')
            ->with('reason')
            ->willReturnSelf();

        $this->warningMock->expects($this->once())
            ->method('setReasonParameters')
            ->with(['param1', 'param2'])
            ->willReturnSelf();

        /** @var \PHPUnit_Framework_MockObject_MockObject|InvalidItemInterface $invalidItemInterfaceMock */
        $invalidItemInterfaceMock = $this->createMock(InvalidItemInterface::class);

        $invalidItemInterfaceMock->expects($this->once())
            ->method('getInvalidData')
            ->willReturn(['data']);

        $this->warningMock->expects($this->once())
            ->method('setItem')
            ->with(['data'])
            ->willReturnSelf();

        $this->warningMock->expects($this->once())
            ->method('setStepExecution')
            ->with($stepExecution)
            ->willReturnSelf();

        $this->warningCollectionMock->expects($this->once())
            ->method('addItem')
            ->with($this->warningMock)
            ->willReturnSelf();

        $this->warningCollectionMock->expects($this->once())
            ->method('save');

        $stepExecution->addWarning(
            'reason',
            ['param1', 'param2'],
            $invalidItemInterfaceMock
        );
    }

    public function testGetSummary()
    {
        $stepExecution = $this->getStepExecutionObject();
        $stepExecution->setData('summary', null);

        $this->assertEquals([], $stepExecution->getSummary());

        $stepExecution->setData('summary', '{"key":"value"}');

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('{"key":"value"}')
            ->willReturn(['key' => 'value']);

        $this->assertEquals(['key' => 'value'], $stepExecution->getSummary());

        $stepExecution->setData('summary', ['key' => 'value']);

        $this->assertEquals(['key' => 'value'], $stepExecution->getSummary());

        $stepExecution->setData('summary', 11);

        $this->assertEquals([], $stepExecution->getSummary());
    }

    public function testGetSummaryInfo()
    {
        $stepExecution = $this->getStepExecutionObject();
        $stepExecution->setData('summary', null);

        $this->assertSame('', $stepExecution->getSummaryInfo('absent'));

        $stepExecution->setData('summary', '{"key":"value"}');

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('{"key":"value"}')
            ->willReturn(['key' => 'value']);

        $this->assertSame('value', $stepExecution->getSummaryInfo('key'));
    }

    public function testAddSummaryInfo()
    {
        $stepExecution = $this->getStepExecutionObject();
        $stepExecution->setData('summary', null);

        $stepExecution->addSummaryInfo('first key', 'first info');

        $this->assertEquals(['first key' => 'first info'], $stepExecution->getData('summary'));

        $stepExecution->addSummaryInfo('second key', 'second info');

        $this->assertEquals(
            ['first key' => 'first info', 'second key' => 'second info'],
            $stepExecution->getSummary()
        );
    }

    public function testIncrementSummaryInfo()
    {
        $stepExecution = $this->getStepExecutionObject();
        $stepExecution->setData('summary', null);

        $stepExecution->incrementSummaryInfo('key', 1);

        $this->assertEquals(['key' => 1], $stepExecution->getData('summary'));

        $stepExecution->incrementSummaryInfo('key');

        $this->assertEquals(['key' => 2], $stepExecution->getData('summary'));
    }

    public function testBeforeSaveStatus()
    {
        $stepExecution = $this->getStepExecutionObject();
        $stepExecution->setData('status', BatchStatus::STARTED());

        $stepExecution->beforeSave();

        $this->assertEquals(
            BatchStatus::STARTED,
            $stepExecution->getData('status')
        );
    }

    public function testBeforeSaveFailureExceptionsWithoutValues()
    {
        $stepExecution = $this->getStepExecutionObject();
        $stepExecution->setData('failure_exceptions', null);

        $this->serializerMock->expects($this->never())
            ->method('serialize');

        $stepExecution->beforeSave();
    }

    public function testBeforeSaveFailureExceptions()
    {
        $stepExecution = $this->getStepExecutionObject();
        $stepExecution->setData('failure_exceptions', [['key' => 'value']]);

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with([['key' => 'value']])
            ->willReturn('[{"key":"value"}]');

        $stepExecution->beforeSave();

        $this->assertEquals(
            '[{"key":"value"}]',
            $stepExecution->getData('failure_exceptions')
        );
    }

    public function testBeforeSaveErrorsWithoutValues()
    {
        $stepExecution = $this->getStepExecutionObject();
        $stepExecution->setData('errors', null);

        $this->serializerMock->expects($this->never())
            ->method('serialize');

        $stepExecution->beforeSave();
    }

    public function testBeforeSaveErrors()
    {
        $stepExecution = $this->getStepExecutionObject();
        $stepExecution->setData('errors', [['key' => 'value']]);

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with([['key' => 'value']])
            ->willReturn('[{"key":"value"}]');

        $stepExecution->beforeSave();

        $this->assertEquals(
            '[{"key":"value"}]',
            $stepExecution->getData('errors')
        );
    }

    public function testBeforeSaveSummaryValues()
    {
        $stepExecution = $this->getStepExecutionObject();
        $stepExecution->setData('summary', null);

        $this->serializerMock->expects($this->never())
            ->method('serialize');

        $stepExecution->beforeSave();
    }

    public function testBeforeSummary()
    {
        $stepExecution = $this->getStepExecutionObject();
        $stepExecution->setData('summary', [['key' => 'value']]);

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with([['key' => 'value']])
            ->willReturn('[{"key":"value"}]');

        $stepExecution->beforeSave();

        $this->assertEquals(
            '[{"key":"value"}]',
            $stepExecution->getData('summary')
        );
    }


}
