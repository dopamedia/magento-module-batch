<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 28.09.17
 */

namespace Dopamedia\Batch\Test\Unit\Job;

use Dopamedia\Batch\Job\JobRegistry;
use Dopamedia\PhpBatch\Job\UndefinedJobException;
use Dopamedia\PhpBatch\JobInterface;
use PHPUnit\Framework\TestCase;

class JobRegistryTest extends TestCase
{
    public function testGetJobThrowsUndefinedJobException()
    {
        $this->expectException(UndefinedJobException::class);
        $this->expectExceptionMessage('The job "absent" is not registered');
        $jobRegistry = new JobRegistry([]);
        $jobRegistry->getJob('absent');
    }

    public function testGetJob()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|JobInterface $jobMock */
        $jobMock = $this->createMock(JobInterface::class);
        $jobMock->expects($this->once())
            ->method('getName')
            ->willReturn('jobName');

        $jobRegistry = new JobRegistry([$jobMock]);

        $this->assertEquals($jobMock, $jobRegistry->getJob('jobName'));
    }

    public function testGetJobs()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|JobInterface $firstJobMock */
        $firstJobMock = $this->createMock(JobInterface::class);

        /** @var \PHPUnit_Framework_MockObject_MockObject|JobInterface $secondJobMock */
        $secondJobMock = $this->createMock(JobInterface::class);

        $jobRegistry = new JobRegistry([$firstJobMock, $secondJobMock]);

        $this->assertEquals([$firstJobMock, $secondJobMock], $jobRegistry->getJobs());


    }

}
