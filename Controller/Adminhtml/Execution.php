<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 08.10.17
 */

namespace Dopamedia\Batch\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * Class Execution
 * @package Dopamedia\Batch\Controller\Adminhtml
 */
abstract class Execution extends Action
{
    const ADMIN_RESOURCE = 'Dopamedia_Batch::batch_execution';

    /**
     * @return Execution
     */
    protected function initAction(): Execution
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Dopamedia_Batch::batch_execution'
        )->_addBreadcrumb(
            __('Batch'),
            __('Batch')
        )->_addBreadcrumb(
            __('Batch Execution'),
            __('Batch Execution')
        );

        return $this;
    }
}