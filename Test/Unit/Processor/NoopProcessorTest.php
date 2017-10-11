<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 29.09.17
 */

namespace Dopamedia\Batch\Test\Unit\Processor;

use Dopamedia\Batch\Processor\NoopProcessor;
use PHPUnit\Framework\TestCase;

class NoopProcessorTest extends TestCase
{
    public function testProcess()
    {
        $noopProcessor = new NoopProcessor();
        $this->assertEquals(['item'], $noopProcessor->process(['item']));
    }
}
