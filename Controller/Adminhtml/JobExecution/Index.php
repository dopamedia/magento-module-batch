<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 08.10.17
 */

namespace Dopamedia\Batch\Controller\Adminhtml\JobExecution;

use Dopamedia\Batch\Controller\Adminhtml\JobExecution;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 * @package Dopamedia\Batch\Controller\Adminhtml\JobExecution
 */
class Index extends JobExecution
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * Index constructor.
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(__('Batch Job Instances'));

        return $resultPage;
    }
}