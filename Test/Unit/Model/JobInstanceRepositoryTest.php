<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 28.09.17
 */

namespace Dopamedia\Batch\Test\Unit\Model;

use Dopamedia\Batch\Model\JobInstance;
use Dopamedia\Batch\Model\JobInstanceRepository;
use Dopamedia\Batch\Model\ResourceModel\JobInstance as ResourceJobInstance;
use Dopamedia\Batch\Model\JobInstanceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;

class JobInstanceRepositoryTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ResourceJobInstance
     */
    protected $resourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobInstanceFactory
     */
    protected $jobInstanceFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobInstance
     */
    protected $jobInstanceMock;

    /**
     * @var JobInstanceRepository
     */
    protected $jobInstanceRepository;

    protected function setUp()
    {
        $this->resourceMock = $this->createMock(ResourceJobInstance::class);

        $this->jobInstanceFactoryMock = $this->getMockBuilder(JobInstanceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->jobInstanceMock = $this->createMock(JobInstance::class);

        $this->jobInstanceRepository = new JobInstanceRepository(
            $this->resourceMock,
            $this->jobInstanceFactoryMock
        );
    }

    public function testGetByIdThrowsNoSuchEntityException()
    {
        $this->jobInstanceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->jobInstanceMock);

        $this->jobInstanceMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('JobInstance with id "123" does not exist.');

        $this->jobInstanceRepository->getById(123);
    }

    public function testGetById()
    {
        $this->jobInstanceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->jobInstanceMock);

        $this->jobInstanceMock->expects($this->once())
            ->method('getId')
            ->willReturn(123);

        $this->assertSame($this->jobInstanceMock, $this->jobInstanceRepository->getById(123));
    }

}
