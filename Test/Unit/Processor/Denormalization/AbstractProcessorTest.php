<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 03.10.17
 */

namespace Dopamedia\Batch\Test\Unit\Processor\Denormalization;

use Dopamedia\Batch\Processor\Denormalization\AbstractProcessor;
use Dopamedia\PhpBatch\Item\InvalidItemException;
use Dopamedia\PhpBatch\StepExecutionInterface;
use Dopamedia\PhpBatch\Item\FileInvalidItem;
use Dopamedia\PhpBatch\Item\FileInvalidItemFactory;
use PHPUnit\Framework\TestCase;

class AbstractProcessorTest extends TestCase
{
    public function testSkipItemWithMessage()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|StepExecutionInterface $stepExecutionMock */
        $stepExecutionMock = $this->createMock(StepExecutionInterface::class);

        /** @var \PHPUnit_Framework_MockObject_MockObject|FileInvalidItemFactory $fileInvalidItemFactoryMock */
        $fileInvalidItemFactoryMock = $this->getMockBuilder(FileInvalidItemFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        /** @var \PHPUnit_Framework_MockObject_MockObject|FileInvalidItem $fileInvalidItemMock */
        $fileInvalidItemMock = $this->createMock(FileInvalidItem::class);

        $stepExecutionMock->expects($this->once())
            ->method('incrementSummaryInfo')
            ->with('skip');

        $stepExecutionMock->expects($this->once())
            ->method('getSummaryInfo')
            ->with('item_position')
            ->willReturn(1);

        $fileInvalidItemFactoryMock->expects($this->once())
            ->method('create')
            ->with(['invalidData' => ['item'], 'itemPosition' => 1])
            ->willReturn($fileInvalidItemMock);

        $this->expectException(InvalidItemException::class);

        $dummyProcessor = new class($fileInvalidItemFactoryMock) extends AbstractProcessor {
            public function process() {
                $this->skipItemWithMessage(['item'], 'message');
            }
        };

        $dummyProcessor->setStepExecution($stepExecutionMock);

        $this->expectException(InvalidItemException::class);

        $dummyProcessor->process();

    }
}
