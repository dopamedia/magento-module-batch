<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 28.09.17
 */

namespace Dopamedia\Batch\Test\Unit\Model;

use Dopamedia\Batch\Model\JobExecution;
use Dopamedia\Batch\Model\JobExecutionFactory;
use Dopamedia\Batch\Model\JobExecutionRepository;
use Dopamedia\Batch\Model\ResourceModel\JobExecution as ResourceJobExecution;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;

class JobExecutionRepositoryTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ResourceJobExecution
     */
    protected $resourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobExecutionFactory
     */
    protected $jobExecutionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobExecution
     */
    protected $jobExecutionMock;

    /**
     * @var JobExecutionRepository
     */
    protected $jobExecutionRepository;

    public function setUp()
    {
        $this->resourceMock = $this->createMock(ResourceJobExecution::class);

        $this->jobExecutionFactoryMock = $this->getMockBuilder(JobExecutionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->jobExecutionMock = $this->createMock(JobExecution::class);

        $this->jobExecutionRepository = new JobExecutionRepository(
            $this->resourceMock,
            $this->jobExecutionFactoryMock
        );
    }

    public function testGetByIdThrowsNoSuchEntityException()
    {
        $this->jobExecutionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->jobExecutionMock);

        $this->jobExecutionMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('JobExecution with id "123" does not exist.');

        $this->jobExecutionRepository->getById(123);
    }

    public function testGetById()
    {
        $this->jobExecutionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->jobExecutionMock);

        $this->jobExecutionMock->expects($this->once())
            ->method('getId')
            ->willReturn(123);

        $this->assertSame($this->jobExecutionMock, $this->jobExecutionRepository->getById(123));
    }
}
