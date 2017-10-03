<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 28.09.17
 */

namespace Dopamedia\Batch\Test\Unit\Console\Command;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Dopamedia\Batch\Console\Command\JobExecuteCommand;
use PHPUnit\Framework\TestCase;

class JobExecuteCommandTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var JobExecuteCommand
     */
    private $command;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->command = $this->objectManager->getObject(JobExecuteCommand::class);
    }

    public function testConfigure()
    {
        $this->assertSame('batch:job:execute', $this->command->getName());

        $commandDefinition = $this->command->getDefinition();

        $this->assertTrue($commandDefinition->hasArgument('code'));
        $this->assertTrue($commandDefinition->getArgument('code')->isRequired());

        $this->assertTrue($commandDefinition->hasOption('config'));
        $this->assertTrue($commandDefinition->getOption('config')->isValueRequired());
    }

}
