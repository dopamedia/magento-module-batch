<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 08.10.17
 */

namespace Dopamedia\Batch\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * Class Profile
 * @package Dopamedia\Batch\Controller\Adminhtml
 */
abstract class Profile extends Action
{
    const ADMIN_RESOURCE = 'Dopamedia_Batch::batch_profile';

    /**
     * @return Profile
     */
    protected function initAction(): Profile
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Dopamedia_Batch::batch_profile'
        )->_addBreadcrumb(
            __('Batch'),
            __('Batch')
        )->_addBreadcrumb(
            __('Batch Profile'),
            __('Batch Profile')
        );

        return $this;
    }
}