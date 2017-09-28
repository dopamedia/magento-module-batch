<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 28.09.17
 */

namespace Dopamedia\Batch\Test\Unit\Model;

use Dopamedia\Batch\Model\StepExecution;
use Dopamedia\Batch\Model\StepExecutionRepository;
use Dopamedia\Batch\Model\ResourceModel\StepExecution as ResourceStepExecution;
use Dopamedia\Batch\Model\StepExecutionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;

class StepExecutionRepositoryTest extends TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ResourceStepExecution
     */
    protected $resourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StepExecutionFactory
     */
    protected $stepExecutionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StepExecution
     */
    protected $stepExecutionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StepExecutionRepository
     */
    protected $stepExecutionRepository;

    protected function setUp()
    {
        $this->resourceMock = $this->createMock(ResourceStepExecution::class);

        $this->stepExecutionFactoryMock = $this->getMockBuilder(StepExecutionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->stepExecutionMock = $this->createMock(StepExecution::class);

        $this->stepExecutionRepository = new StepExecutionRepository(
            $this->resourceMock,
            $this->stepExecutionFactoryMock
        );
    }

    public function testGetByIdThrowsNoSuchEntityException()
    {
        $this->stepExecutionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->stepExecutionMock);

        $this->stepExecutionMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('StepExecution with id "123" does not exist.');

        $this->stepExecutionRepository->getById(123);
    }

    public function testGetById()
    {
        $this->stepExecutionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->stepExecutionMock);

        $this->stepExecutionMock->expects($this->once())
            ->method('getId')
            ->willReturn(123);

        $this->assertSame($this->stepExecutionMock, $this->stepExecutionRepository->getById(123));
    }



}
