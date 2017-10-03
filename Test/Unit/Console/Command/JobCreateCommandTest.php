<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 28.09.17
 */

namespace Dopamedia\Batch\Test\Unit\Console\Command;

use Dopamedia\Batch\Api\JobInstanceRepositoryInterface;
use Dopamedia\Batch\Console\Command\JobCreateCommand;
use Dopamedia\PhpBatch\Job\JobParameters;
use Dopamedia\PhpBatch\Job\JobParametersFactory;
use Dopamedia\PhpBatch\Job\JobRegistryInterface;
use Dopamedia\PhpBatch\Job\UndefinedJobException;
use Dopamedia\PhpBatch\JobInstanceInterface;
use Dopamedia\PhpBatch\JobInstanceInterfaceFactory;
use Dopamedia\PhpBatch\JobInterface;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Phrase;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;

class JobCreateCommandTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobInstanceInterfaceFactory
     */
    protected $jobInstanceFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobInstanceInterface
     */
    protected $jobInstanceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobRegistryInterface
     */
    protected $jobRegistryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobInterface
     */
    protected $jobMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobInstanceRepositoryInterface
     */
    protected $jobInstanceRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobParametersFactory
     */
    protected $jobParametersFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobParameters
     */
    protected $jobParametersMock;

    /**
     * @var JobCreateCommand
     */
    protected $command;

    protected function setUp()
    {
        $this->jobInstanceFactoryMock = $this->getMockBuilder(JobInstanceInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->jobInstanceMock = $this->createMock(JobInstanceInterface::class);

        $this->jobRegistryMock = $this->createMock(JobRegistryInterface::class);

        $this->jobMock = $this->createMock(JobInterface::class);

        $this->jobInstanceRepositoryMock = $this->createMock(JobInstanceRepositoryInterface::class);

        $this->jobParametersFactoryMock = $this->createMock(JobParametersFactory::class);

        $this->jobParametersMock = $this->createMock(JobParameters::class);

        $this->command = new JobCreateCommand(
            $this->jobInstanceFactoryMock,
            $this->jobRegistryMock,
            $this->jobInstanceRepositoryMock,
            $this->jobParametersFactoryMock
        );
    }

    public function testConfigure()
    {
        $this->assertSame('batch:job:create', $this->command->getName());

        $commandDefinition = $this->command->getDefinition();

        $this->assertTrue($commandDefinition->hasArgument('job'));
        $this->assertTrue($commandDefinition->getArgument('job')->isRequired());

        $this->assertTrue($commandDefinition->hasArgument('code'));
        $this->assertTrue($commandDefinition->getArgument('code')->isRequired());

        $this->assertTrue($commandDefinition->hasOption('config'));
        $this->assertTrue($commandDefinition->getOption('config')->isValueRequired());
    }

    public function testExecuteWithAbsentJob()
    {
        $commandTester = new CommandTester($this->command);

        $this->jobInstanceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->jobInstanceMock);

        $this->jobInstanceMock->expects($this->once())
            ->method('setJobName')
            ->willReturnSelf();

        $this->jobInstanceMock->expects($this->once())
            ->method('setCode')
            ->willReturnSelf();

        $this->jobInstanceMock->expects($this->once())
            ->method('getJobName')
            ->willReturn('job');

        $this->jobRegistryMock->expects($this->once())
            ->method('getJob')
            ->willThrowException(new UndefinedJobException(new Phrase('exception message')));

        $commandTester->execute(['job' => 'job', 'code' => 'code']);

        $this->assertEquals(Cli::RETURN_FAILURE, $commandTester->getStatusCode());
        $this->assertContains('exception message', $commandTester->getDisplay());
    }

    public function testExecuteWithCouldNotSaveException()
    {
        $commandTester = new CommandTester($this->command);

        $this->jobInstanceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->jobInstanceMock);

        $this->jobInstanceMock->expects($this->once())
            ->method('setJobName')
            ->willReturnSelf();

        $this->jobInstanceMock->expects($this->once())
            ->method('setCode')
            ->willReturnSelf();

        $this->jobInstanceMock->expects($this->once())
            ->method('getJobName')
            ->willReturn('job');

        $this->jobRegistryMock->expects($this->once())
            ->method('getJob')
            ->willReturn($this->jobMock);

        $this->jobParametersFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->jobParametersMock);

        $this->jobInstanceMock->expects($this->once())
            ->method('setRawParameters')
            ->willReturnSelf();

        $this->jobInstanceRepositoryMock->expects($this->once())
            ->method('save')
            ->willThrowException(new CouldNotSaveException(new Phrase('exception message')));

        $commandTester->execute(['job' => 'job', 'code' => 'code']);

        $this->assertEquals(Cli::RETURN_FAILURE, $commandTester->getStatusCode());
        $this->assertContains('exception message', $commandTester->getDisplay());
    }

    public function testExecute()
    {
        $commandTester = new CommandTester($this->command);

        $this->jobInstanceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->jobInstanceMock);

        $this->jobInstanceMock->expects($this->once())
            ->method('setJobName')
            ->with('job')
            ->willReturnSelf();

        $this->jobInstanceMock->expects($this->once())
            ->method('setCode')
            ->with('code')
            ->willReturnSelf();

        $this->jobInstanceMock->expects($this->once())
            ->method('getJobName')
            ->willReturn('job');

        $this->jobRegistryMock->expects($this->once())
            ->method('getJob')
            ->willReturn($this->jobMock);

        $this->jobParametersFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->jobParametersMock);

        $this->jobInstanceMock->expects($this->once())
            ->method('setRawParameters')
            ->willReturnSelf();

        $this->jobInstanceRepositoryMock->expects($this->once())
            ->method('save')
            ->willReturn($this->jobInstanceMock);

        $this->jobInstanceMock->expects($this->once())
            ->method('getId')
            ->willReturn(123);

        $commandTester->execute(['job' => 'job', 'code' => 'code']);

        $this->assertEquals(Cli::RETURN_SUCCESS, $commandTester->getStatusCode());
        $this->assertContains('JobInstance with id "123" has been created', $commandTester->getDisplay());
    }


}
