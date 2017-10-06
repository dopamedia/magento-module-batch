<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 27.09.17
 */

namespace Dopamedia\Batch\Test\Unit\Model;

use Dopamedia\Batch\Api\StepExecutionRepositoryInterface;
use Dopamedia\Batch\Model\Warning;
use Dopamedia\PhpBatch\Repository\JobRepositoryInterface;
use Dopamedia\PhpBatch\StepExecutionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class WarningTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobRepositoryInterface
     */
    protected $jobRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StepExecutionInterface
     */
    protected $stepExecutionMock;

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
        $this->jobRepository = $this->createMock(JobRepositoryInterface::class);
        $this->stepExecutionMock = $this->createMock(StepExecutionInterface::class);
        $this->serializerMock = $this->createMock(Serializer::class);
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @return object|Warning
     */
    protected function getWarningObject()
    {
        return $this->objectManager->getObject(
            Warning::class,
            [
                'jobRepository' => $this->jobRepository,
                'serializer' => $this->serializerMock
            ]
        );
    }

    public function testGetStepExecutionWithoutStepExecutionId()
    {
        $warning = $this->getWarningObject();
        $warning->setData('step_execution_id', null);

        $this->assertNull($warning->getStepExecution());
    }

    public function testGetStepExecutionWithNoSuchEntityException()
    {
        $this->jobRepository->expects($this->once())
            ->method('getStepExecutionById')
            ->with(11)
            ->willThrowException(new NoSuchEntityException(new Phrase('')));

        $warning = $this->getWarningObject();
        $warning->setData('step_execution_id', 11);

        $this->expectException(NoSuchEntityException::class);

        $warning->getStepExecution();
    }

    public function testGetStepExecution()
    {
        $this->jobRepository->expects($this->once())
            ->method('getStepExecutionById')
            ->with(11)
            ->willReturn($this->stepExecutionMock);

        $warning = $this->getWarningObject();
        $warning->setData('step_execution_id', 11);

        $this->assertSame($this->stepExecutionMock, $warning->getStepExecution());
    }

    public function testSetStepExecution()
    {
        $this->stepExecutionMock->expects($this->once())
            ->method('getId')
            ->willReturn(11);

        $warning = $this->getWarningObject();
        $warning->setStepExecution($this->stepExecutionMock);

        $this->assertEquals(11, $warning->getStepExecutionId());
    }

    public function testGetReasonParameters()
    {
        $warning = $this->getWarningObject();

        $this->assertEquals([], $warning->getReasonParameters());

        $warning->setData('reason_parameters', '{"key":"value"}');

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('{"key":"value"}')
            ->willReturn(['key' => 'value']);

        $this->assertEquals(['key' => 'value'], $warning->getReasonParameters());

        $warning->setData('reason_parameters', ['key' => 'value']);

        $this->assertEquals(['key' => 'value'], $warning->getReasonParameters());

        $warning->setData('reason_parameters', 11);

        $this->assertEquals([], $warning->getReasonParameters());
    }

    public function testGetItem()
    {
        $warning = $this->getWarningObject();

        $this->assertEquals([], $warning->getItem());

        $warning->setData('item', '{"key":"value"}');

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('{"key":"value"}')
            ->willReturn(['key' => 'value']);

        $this->assertEquals(['key' => 'value'], $warning->getItem());

        $warning->setData('item', ['key' => 'value']);

        $this->assertEquals(['key' => 'value'], $warning->getItem());

        $warning->setData('item', 11);

        $this->assertEquals([], $warning->getItem());
    }

    public function testBeforeSaveReasonParametersWithoutValues()
    {
        $warning = $this->getWarningObject();
        $warning->setData('reason_parameters', null);

        $this->serializerMock->expects($this->never())
            ->method('serialize');

        $warning->beforeSave();
    }

    public function testBeforeSaveReasonParameters()
    {
        $warning = $this->getWarningObject();
        $warning->setData('reason_parameters', [['key' => 'value']]);

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with([['key' => 'value']])
            ->willReturn('[{"key":"value"}]');

        $warning->beforeSave();

        $this->assertEquals(
            '[{"key":"value"}]',
            $warning->getData('reason_parameters')
        );
    }

    public function testBeforeSaveItemWithoutValues()
    {
        $warning = $this->getWarningObject();
        $warning->setData('item', null);

        $this->serializerMock->expects($this->never())
            ->method('serialize');

        $warning->beforeSave();
    }

    public function testBeforeSaveItem()
    {
        $warning = $this->getWarningObject();
        $warning->setData('item', [['key' => 'value']]);

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with([['key' => 'value']])
            ->willReturn('[{"key":"value"}]');

        $warning->beforeSave();

        $this->assertEquals(
            '[{"key":"value"}]',
            $warning->getData('item')
        );
    }
}
