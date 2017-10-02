<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 02.10.17
 */

namespace Dopamedia\Batch\Test\Unit\Reader\File;

use Box\Spout\Reader\SheetInterface;
use Box\Spout\Reader\IteratorInterface;
use Dopamedia\Batch\Reader\File\NumericHeaderProvider;
use PHPUnit\Framework\TestCase;

class NumericHeaderProviderTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SheetInterface
     */
    protected $sheetMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|IteratorInterface
     */
    protected $iteratorMock;

    protected function setUp()
    {
        $this->sheetMock = $this->createMock(SheetInterface::class);
        $this->iteratorMock = $this->createMock(IteratorInterface::class);
    }

    public function testGetHeaders()
    {
        $numericHeaderProvider = new NumericHeaderProvider();

        $this->sheetMock->expects($this->once())
            ->method('getRowIterator')
            ->willReturn($this->iteratorMock);

        $this->iteratorMock->expects($this->once())
            ->method('current')
            ->willReturn([0, 1]);

        $this->assertEquals([0, 1], $numericHeaderProvider->getHeaders($this->sheetMock));
    }
}
