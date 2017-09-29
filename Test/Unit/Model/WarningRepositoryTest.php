<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 28.09.17
 */

namespace Dopamedia\Batch\Test\Unit\Model;

use Dopamedia\Batch\Model\Warning;
use Dopamedia\Batch\Model\WarningRepository;
use Dopamedia\Batch\Model\ResourceModel\Warning as ResourceWarning;
use Dopamedia\Batch\Model\WarningFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;

class WarningRepositoryTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ResourceWarning
     */
    protected $resourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|WarningFactory
     */
    protected $warningFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Warning
     */
    protected $warningMock;

    /**
     * @var WarningRepository
     */
    protected $warningRepository;

    protected function setUp()
    {
        $this->resourceMock = $this->createMock(ResourceWarning::class);

        $this->warningFactoryMock = $this->getMockBuilder(WarningFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->warningMock = $this->createMock(Warning::class);

        $this->warningRepository = new WarningRepository(
            $this->resourceMock,
            $this->warningFactoryMock
        );
    }

    public function testGetByIdThrowsNoSuchEntityException()
    {
        $this->warningFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->warningMock);

        $this->warningMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('Warning with id "123" does not exist.');

        $this->warningRepository->getById(123);
    }

    public function testGetById()
    {
        $this->warningFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->warningMock);

        $this->warningMock->expects($this->once())
            ->method('getId')
            ->willReturn(123);

        $this->assertSame($this->warningMock, $this->warningRepository->getById(123));
    }

    public function testSaveThrowsCouldNotSaveException()
    {
        $this->resourceMock->expects($this->once())
            ->method('save')
            ->with($this->warningMock)
            ->willThrowException(new \Exception('exception message'));

        $this->expectException(CouldNotSaveException::class);
        $this->expectExceptionMessage('exception message');

        $this->warningRepository->save($this->warningMock);
    }

    public function testSave()
    {
        $this->resourceMock->expects($this->once())
            ->method('save')
            ->with($this->warningMock)
            ->willReturn($this->warningMock);

        $this->assertSame(
            $this->warningMock,
            $this->warningRepository->save($this->warningMock)
        );
    }

}
