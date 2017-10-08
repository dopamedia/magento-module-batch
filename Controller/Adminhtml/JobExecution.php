<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 08.10.17
 */

namespace Dopamedia\Batch\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;

/**
 * Class JobExecution
 * @package Dopamedia\Batch\Controller\Adminhtml
 */
abstract class JobExecution extends Action
{
    const ADMIN_RESOURCE = 'Dopamedia_Batch::batch_job_execution';

    /**
     * @param Page $page
     * @return Page
     */
    protected function initPage(Page $page): Page
    {
        $page->setActiveMenu('Dopamedia_Batch::batch_job_execution')
            ->addBreadcrumb(__('Batch'), __('Batch'))
            ->addBreadcrumb(__('Batch Job Execution'), __('Batch Job Execution'));

        return $page;
    }
}