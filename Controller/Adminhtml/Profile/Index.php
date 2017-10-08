<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 08.10.17
 */

namespace Dopamedia\Batch\Controller\Adminhtml\Profile;

use Dopamedia\Batch\Controller\Adminhtml\Profile;

/**
 * Class Index
 * @package Dopamedia\Batch\Controller\Adminhtml\JobInstance
 */
class Index extends Profile
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        $this->initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Batch Profiles'));
        $this->_view->renderLayout();
    }
}