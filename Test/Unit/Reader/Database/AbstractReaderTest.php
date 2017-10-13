<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 03.10.17
 */

namespace Dopamedia\Batch\Test\Unit\Reader\Database;

use Dopamedia\Batch\Reader\Database\AbstractReader;
use Dopamedia\PhpBatch\StepExecutionInterface;
use PHPUnit\Framework\TestCase;

class AbstractReaderTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StepExecutionInterface
     */
    protected $stepExecutionMock;

    protected function setUp()
    {
        $this->stepExecutionMock = $this->createMock(StepExecutionInterface::class);
    }

    public function testReadNoResults()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|\ArrayIterator $arrayIteratorMock */
        $arrayIteratorMock = $this->createMock(\ArrayIterator::class);

        $arrayIteratorMock->expects($this->once())
            ->method('current')
            ->willReturn(null);

        $arrayIteratorMock->expects($this->never())
            ->method('next');

        $dummyReader = new class($arrayIteratorMock) extends AbstractReader {

            protected $arrayIterator;

            public function __construct(\ArrayIterator $arrayIterator)
            {
                $this->arrayIterator = $arrayIterator;
            }

            protected function getResults(): \ArrayIterator
            {
                return $this->arrayIterator;
            }
        };

        $dummyReader->read();
    }


    public function testRead()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|\ArrayIterator $arrayIteratorMock */
        $arrayIteratorMock = $this->createMock(\ArrayIterator::class);

        $arrayIteratorMock->expects($this->once())
            ->method('current')
            ->willReturn(['data']);

        $arrayIteratorMock->expects($this->once())
            ->method('next');

        $this->stepExecutionMock->expects($this->once())
            ->method('incrementSummaryInfo')
            ->with('read');

        $dummyReader = new class($arrayIteratorMock) extends AbstractReader {

            protected $arrayIterator;

            public function __construct(\ArrayIterator $arrayIterator)
            {
                $this->arrayIterator = $arrayIterator;
            }

            protected function getResults(): \ArrayIterator
            {
                return $this->arrayIterator;
            }
        };

        $dummyReader->setStepExecution($this->stepExecutionMock);

        $dummyReader->read();
    }
}
