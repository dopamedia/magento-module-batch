<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 29.09.17
 */

namespace Dopamedia\Batch\Test\Unit\Writer;

use Dopamedia\Batch\Writer\NoopWriter;
use PHPUnit\Framework\TestCase;

class NoopWriterTest extends TestCase
{
    public function testWrite()
    {
        $noopWriter = new NoopWriter();
        $this->assertNull($noopWriter->write([]));
    }
}
