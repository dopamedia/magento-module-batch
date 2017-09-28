<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 27.09.17
 */

namespace Dopamedia\Batch\Test\Unit\Model;

use Dopamedia\Batch\Model\JobExecution;
use Dopamedia\Batch\Model\JobInstance;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Dopamedia\Batch\Model\ResourceModel\JobExecution\Collection as JobExecutionCollection;
use Dopamedia\Batch\Model\ResourceModel\JobExecution\CollectionFactory as JobExecutionCollectionFactory;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use PHPUnit\Framework\TestCase;

class JobInstanceTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobExecutionCollectionFactory
     */
    protected $jobExecutionCollectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobExecutionCollection
     */
    protected $jobExecutionCollectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobExecution
     */
    protected $jobExecutionMock;

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
        $this->jobExecutionCollectionFactoryMock = $this->getMockBuilder(JobExecutionCollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->jobExecutionCollectionMock = $this->createMock(JobExecutionCollection::class);

        $this->jobExecutionMock = $this->createMock(JobExecution::class);

        $this->serializerMock = $this->createMock(Serializer::class);

        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @return object|JobInstance
     */
    protected function getJobInstanceObject()
    {
        return $this->objectManager->getObject(
            JobInstance::class,
            [
                'jobExecutionCollectionFactory' => $this->jobExecutionCollectionFactoryMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    public function testGetRawParameters()
    {
        $jobInstance = $this->getJobInstanceObject();

        $this->assertEquals([], $jobInstance->getRawParameters());

        $jobInstance->setData('raw_parameters', '{"key":"value"}');

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('{"key":"value"}')
            ->willReturn(['key' => 'value']);

        $this->assertEquals(['key' => 'value'], $jobInstance->getRawParameters());

        $jobInstance->setData('raw_parameters', ['key' => 'value']);

        $this->assertEquals(['key' => 'value'], $jobInstance->getRawParameters());

        $jobInstance->setData('raw_parameters', 11);

        $this->assertEquals([], $jobInstance->getRawParameters());
    }

    public function testGetJobExecutions()
    {
        $jobInstance = $this->getJobInstanceObject();

        $this->jobExecutionCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->jobExecutionCollectionMock);

        $this->jobExecutionCollectionMock->expects($this->once())
            ->method('setJobInstanceFilter')
            ->with($jobInstance)
            ->willReturnSelf();

        $this->jobExecutionCollectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([]);

        $jobInstance->getJobExecutions();
    }

    public function testAddJobExecution()
    {
        $jobInstance = $this->getJobInstanceObject();

        $this->jobExecutionCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->jobExecutionCollectionMock);

        $this->jobExecutionCollectionMock->expects($this->once())
            ->method('setJobInstanceFilter')
            ->with($jobInstance)
            ->willReturnSelf();

        $this->jobExecutionCollectionMock->expects($this->once())
            ->method('addItem')
            ->with($this->jobExecutionMock);

        $jobInstance->addJobExecution($this->jobExecutionMock);
    }

    public function testRemoveJobExecution()
    {
        $jobInstance = $this->getJobInstanceObject();

        $this->jobExecutionCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->jobExecutionCollectionMock);

        $this->jobExecutionCollectionMock->expects($this->once())
            ->method('setJobInstanceFilter')
            ->with($jobInstance)
            ->willReturnSelf();

        $this->jobExecutionCollectionMock->expects($this->once())
            ->method('removeItemByKey')
            ->with(123);

        $this->jobExecutionMock->expects($this->once())
            ->method('getId')
            ->willReturn(123);

        $jobInstance->removeJobExecution($this->jobExecutionMock);

    }

    public function testBeforeSaveRawParametersWithoutValues()
    {
        $jobInstance = $this->getJobInstanceObject();
        $jobInstance->setData('raw_parameters', null);

        $this->serializerMock->expects($this->never())
            ->method('serialize');

        $jobInstance->beforeSave();
    }

    public function testBeforeSaveRawParameters()
    {
        $jobInstance = $this->getJobInstanceObject();
        $jobInstance->setData('raw_parameters', [['key' => 'value']]);

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with([['key' => 'value']]);

        $jobInstance->beforeSave();
    }

}
