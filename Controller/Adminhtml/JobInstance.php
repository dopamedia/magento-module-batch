<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 08.10.17
 */

namespace Dopamedia\Batch\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;

/**
 * Class JobInstance
 * @package Dopamedia\Batch\Controller\Adminhtml
 */
abstract class JobInstance extends Action
{
    const ADMIN_RESOURCE = 'Dopamedia_Batch::batch_profile';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * JobInstance constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    )
    {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * @param Page $page
     * @return Page
     */
    protected function initPage(Page $page): Page
    {
        $page->setActiveMenu('Dopamedia_Batch::batch_job_instance')
            ->addBreadcrumb(__('Batch'), __('Batch'))
            ->addBreadcrumb(__('Batch Job Instance'), __('Batch Job Instance'));

        return $page;
    }

}