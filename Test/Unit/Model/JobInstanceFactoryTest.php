<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 08.10.17
 */

namespace Dopamedia\Batch\Test\Unit\Model;

use Dopamedia\Batch\Model\JobInstance;
use Dopamedia\Batch\Model\JobInstanceFactory;
use Dopamedia\PhpBatch\JobInstanceInterface;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;

class JobInstanceFactoryTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ObjectManagerInterface
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobInstanceInterface
     */
    protected $jobInstanceMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->jobInstanceMock = $this->createMock(JobInstanceInterface::class);
    }

    public function testCreate()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(JobInstance::class)
            ->willReturn($this->jobInstanceMock);

        $jobInstanceFactory = new JobInstanceFactory($this->objectManagerMock);

        $jobInstanceFactory->create();

    }

}
