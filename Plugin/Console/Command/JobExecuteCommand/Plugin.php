<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 13.10.17
 */

namespace Dopamedia\Batch\Plugin\Console\Command\JobExecuteCommand;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\State;

/**
 * Class Plugin
 * @package Dopamedia\Batch\Plugin\Console\Command\JobExecuteCommand
 */
class Plugin
{
    /**
     * @var State
     */
    private $state;

    /**
     * Plugin constructor.
     * @param State $state
     */
    public function __construct(State $state)
    {
        $this->state = $state;
    }

    /**
     * @return void
     */
    public function beforeRun(): void
    {
        try {
            $this->state->setAreaCode(FrontNameResolver::AREA_CODE);
        } catch (\Exception $e) {
            // noop
        }
    }
}