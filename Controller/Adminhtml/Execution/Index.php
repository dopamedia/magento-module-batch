<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 08.10.17
 */

namespace Dopamedia\Batch\Controller\Adminhtml\Execution;

use Dopamedia\Batch\Controller\Adminhtml\Execution;

/**
 * Class Index
 * @package Dopamedia\Batch\Controller\Adminhtml\Execution
 */
class Index extends Execution
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        $this->initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Batch Executions'));
        $this->_view->renderLayout();
    }
}