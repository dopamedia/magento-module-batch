<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 08.10.17
 */

namespace Dopamedia\Batch\Controller\Adminhtml\JobInstance;

use Dopamedia\Batch\Controller\Adminhtml\JobInstance;
use Magento\Backend\App\Action;
use Magento\Framework\Registry as CoreRegistry;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 * @package Dopamedia\Batch\Controller\Adminhtml\JobInstance
 */
class Index extends JobInstance
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @inheritDoc
     */
    public function __construct(
        Action\Context $context,
        CoreRegistry $coreRegistry,
        PageFactory $resultPageFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $coreRegistry);
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