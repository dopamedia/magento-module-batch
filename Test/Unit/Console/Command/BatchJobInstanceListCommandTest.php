<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 03.10.17
 */

namespace Dopamedia\Batch\Test\Unit\Console\Command;

use Dopamedia\Batch\Console\Command\BatchJobInstanceListCommand;
use Dopamedia\Batch\Model\JobInstance;
use Dopamedia\Batch\Model\ResourceModel\JobInstance\Collection as JobInstanceCollection;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Helper\TableFactory;
use Symfony\Component\Console\Helper\Table;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class BatchJobInstanceListCommandTest
 * @package Dopamedia\Batch\Test\Unit\Console\Command
 * @group current
 */
class BatchJobInstanceListCommandTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobInstanceCollection
     */
    protected $jobInstanceCollectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TableFactory
     */
    protected $tableFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Table
     */
    protected $tableMock;

    /**
     * @var BatchJobInstanceListCommand
     */
    protected $command;

    protected function setUp()
    {
        $this->jobInstanceCollectionMock = $this->createMock(JobInstanceCollection::class);

        $this->tableFactoryMock = $this->getMockBuilder(TableFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->tableMock = $this->createMock(Table::class);

        $this->command = new BatchJobInstanceListCommand(
            $this->jobInstanceCollectionMock,
            $this->tableFactoryMock
        );
    }

    public function testConfigure()
    {
        $this->assertSame('batch:job-instance:list', $this->command->getName());
    }

    public function testExecuteWithoutInstances()
    {
        $this->jobInstanceCollectionMock->expects($this->once())
            ->method('count')
            ->willReturn(0);

        $commandTester = new CommandTester($this->command);

        $commandTester->execute([]);

        $this->assertEquals(Cli::RETURN_FAILURE, $commandTester->getStatusCode());
        $this->assertContains('No JobInstances defined', $commandTester->getDisplay());

    }

    public function testExecute()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|JobInstance $jobInstanceMock */
        $jobInstanceMock = $this->createMock(JobInstance::class);

        $this->jobInstanceCollectionMock->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $this->tableFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->tableMock);

        $this->jobInstanceCollectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$jobInstanceMock]);

        $jobInstanceMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $jobInstanceMock->expects($this->once())
            ->method('getCode')
            ->willReturn('code');

        $jobInstanceMock->expects($this->once())
            ->method('getJobName')
            ->willReturn('jobName');

        $this->tableMock->expects($this->once())
            ->method('render');

        $commandTester = new CommandTester($this->command);

        $commandTester->execute([]);

        $this->assertEquals(Cli::RETURN_SUCCESS, $commandTester->getStatusCode());
    }
}
