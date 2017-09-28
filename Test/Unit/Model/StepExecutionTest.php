<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 28.09.17
 */

namespace Dopamedia\Batch\Test\Unit\Model;

use Dopamedia\Batch\Api\JobExecutionRepositoryInterface;
use Dopamedia\Batch\Model\StepExecution;
use Dopamedia\PhpBatch\BatchStatus;
use Dopamedia\PhpBatch\ExitStatus;
use Dopamedia\PhpBatch\Job\RuntimeErrorException;
use Dopamedia\PhpBatch\JobExecutionInterface;
use Dopamedia\PhpBatch\JobParameters;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class StepExecutionTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobExecutionRepositoryInterface
     */
    protected $jobExecutionRepositoryMock;

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
     * @var ObjectManager
     */
    protected $objectManager;


    protected function setUp()
    {
        $this->jobExecutionRepositoryMock = $this->createMock(JobExecutionRepositoryInterface::class);
        $this->jobExecutionMock = $this->createMock(JobExecutionInterface::class);
        $this->jobParametersMock = $this->createMock(JobParameters::class);
        $this->serializerMock = $this->createMock(Serializer::class);
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
                'jobExecutionRepository' => $this->jobExecutionRepositoryMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    public function testGetJobExecutionThrowsLocalizedException()
    {
        $this->expectException(LocalizedException::class);

        $this->jobExecutionRepositoryMock->expects($this->once())
            ->method('getById')
            ->willThrowException(new LocalizedException(new Phrase('')));

        $stepExecution = $this->getStepExecutionObject();
        $stepExecution->setJobExecutionId(123);

        $stepExecution->getJobExecution();
    }

    public function testGetJobExecution()
    {
        $this->jobExecutionRepositoryMock->expects($this->once())
            ->method('getById')
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

    public function testGetFailureExecptions()
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


}
