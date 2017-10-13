<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 12.10.17
 */

namespace Dopamedia\Batch\Test\Unit\Model\Import\Source;

use Dopamedia\Batch\Model\Import\Source\ArraySource;
use PHPUnit\Framework\TestCase;

class ArraySourceTest extends TestCase
{
    /**
     * @var array
     */
    protected $data = [['first header' => 'first value'], ['second header' => 'second value']];

    /**
     * @var ArraySource
     */
    protected $arraySource;

    protected function setUp()
    {
        $this->arraySource = new ArraySource($this->data);
    }

    public function testCurrent()
    {
        $this->assertEquals(['first header' => 'first value'], $this->arraySource->current());
    }

    public function testNext()
    {
        $this->arraySource->next();
        $this->assertEquals(['second header' => 'second value'], $this->arraySource->current());
    }

    public function testKey()
    {
        $this->assertEquals(0, $this->arraySource->key());
        $this->arraySource->next();
        $this->assertEquals(1, $this->arraySource->key());
    }

    public function testValid()
    {
        $this->assertTrue($this->arraySource->valid());
        $this->arraySource->next();
        $this->assertTrue($this->arraySource->valid());
        $this->arraySource->next();
        $this->assertFalse($this->arraySource->valid());
    }

    public function testRewind()
    {
        $this->arraySource->next();
        $this->arraySource->rewind();
        $this->assertEquals(0, $this->arraySource->key());
    }

    public function testSeekThrowsException()
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('invalid seek position 100');

        $this->arraySource->seek(100);
    }

    public function testSeek()
    {
        $this->arraySource->seek(1);
        $this->assertEquals(1, $this->arraySource->key());
    }

    public function testGetColNames()
    {
        $this->assertEquals(['first header', 'second header'], $this->arraySource->getColNames());
    }

    public function testGetNextRow()
    {
        $data = $this->data;

        $dummyArraySource = new class ($data) extends ArraySource {
            public function getNextRow()
            {
                return $this->_getNextRow();
            }
        };

        $this->assertEquals(['second header' => 'second value'], $dummyArraySource->getNextRow());

    }
}
