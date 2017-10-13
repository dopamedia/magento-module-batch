<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 13.10.17
 */

namespace Dopamedia\Batch\Test\Unit\Plugin\Console\Command\JobExecuteCommand;

use Dopamedia\Batch\Plugin\Console\Command\JobExecuteCommand\Plugin;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\State;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|State
     */
    protected $stateMock;

    /**
     * @var Plugin
     */
    protected $plugin;

    protected function setUp()
    {
        $this->stateMock = $this->createMock(State::class);
        $this->plugin = new Plugin($this->stateMock);
    }

    public function testBeforeRunWithException()
    {
        $this->stateMock->expects($this->once())
            ->method('setAreaCode')
            ->with(FrontNameResolver::AREA_CODE)
            ->willThrowException(new \Exception());

        $this->plugin->beforeRun();
    }

    public function testBeforeRun()
    {
        $this->stateMock->expects($this->once())
            ->method('setAreaCode')
            ->with(FrontNameResolver::AREA_CODE);

        $this->plugin->beforeRun();
    }
}
