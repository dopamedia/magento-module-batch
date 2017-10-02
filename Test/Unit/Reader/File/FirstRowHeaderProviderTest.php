<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 02.10.17
 */

namespace Dopamedia\Batch\Test\Unit\Reader\File;

use Box\Spout\Reader\SheetInterface;
use Box\Spout\Reader\IteratorInterface;
use Dopamedia\Batch\Reader\File\FirstRowHeaderProvider;
use PHPUnit\Framework\TestCase;

class FirstRowHeaderProviderTest extends TestCase
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
        $firstRowHeaderProvider = new FirstRowHeaderProvider();

        $this->sheetMock->expects($this->once())
            ->method('getRowIterator')
            ->willReturn($this->iteratorMock);

        $this->iteratorMock->expects($this->once())
            ->method('current')
            ->willReturn(['the', 'header']);

        $this->assertEquals(
            ['the', 'header'],
            $firstRowHeaderProvider->getHeaders($this->sheetMock)
        );
    }
}
