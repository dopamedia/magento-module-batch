<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 03.10.17
 */

namespace Dopamedia\Batch\Test\Unit\Step;

use Dopamedia\Batch\Item\CharsetValidator;
use Dopamedia\Batch\Step\ValidatorStep;
use Dopamedia\PhpBatch\Adapter\EventManagerAdapterInterface;
use Dopamedia\PhpBatch\Repository\JobRepositoryInterface;
use Dopamedia\PhpBatch\StepExecutionInterface;
use PHPUnit\Framework\TestCase;

class ValidatorStepTest extends TestCase
{
    public function testDoExecute()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|EventManagerAdapterInterface $eventManagerAdapterMock */
        $eventManagerAdapterMock = $this->createMock(EventManagerAdapterInterface::class);

        /** @var \PHPUnit_Framework_MockObject_MockObject|JobRepositoryInterface $jobRepositoryMock */
        $jobRepositoryMock = $this->createMock(JobRepositoryInterface::class);

        /** @var \PHPUnit_Framework_MockObject_MockObject|CharsetValidator $charsetValidatorMock */
        $charsetValidatorMock = $this->createMock(CharsetValidator::class);

        /** @var \PHPUnit_Framework_MockObject_MockObject|StepExecutionInterface $stepExecutionMock */
        $stepExecutionMock = $this->createMock(StepExecutionInterface::class);

        $charsetValidatorMock->expects($this->once())
            ->method('setStepExecution')
            ->with($stepExecutionMock);

        $charsetValidatorMock->expects($this->once())
            ->method('validate');

        $dummyValidatorStep = new class(
            'name',
            $eventManagerAdapterMock,
            $jobRepositoryMock,
            $charsetValidatorMock
        ) extends ValidatorStep {
            public function execute(StepExecutionInterface $stepExecution): void {
                $this->doExecute($stepExecution);
            }
        };

        $dummyValidatorStep->execute($stepExecutionMock);

    }

}
