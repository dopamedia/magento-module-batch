<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 29.09.17
 */

namespace Dopamedia\Batch\Test\Unit\Reader;

use Dopamedia\Batch\Reader\NoopReader;
use PHPUnit\Framework\TestCase;

class NoopReaderTest extends TestCase
{
    public function testRead()
    {
        $noopReader = new NoopReader();
        $this->assertNull($noopReader->read());
    }
}
