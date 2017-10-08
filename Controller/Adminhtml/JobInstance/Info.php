<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 08.10.17
 */

namespace Dopamedia\Batch\Controller\Adminhtml\JobInstance;

use Dopamedia\Batch\Controller\Adminhtml\JobInstance;
use Dopamedia\Batch\Controller\Adminhtml\RegistryConstants;
use Dopamedia\Batch\Model\JobRepository;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry as CoreRegistry;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Info
 * @package Dopamedia\Batch\Controller\Adminhtml\JobInstance
 */
class Info extends JobInstance
{
    /**
     * @var JobRepository
     */
    private $jobRepository;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * Info constructor.
     * @param Action\Context $context
     * @param JobRepository $jobRepository
     * @param CoreRegistry $coreRegistry
     */
    public function __construct(
        Action\Context $context,
        JobRepository $jobRepository,
        CoreRegistry $coreRegistry,
        PageFactory $resultPageFactory
    )
    {
        $this->jobRepository = $jobRepository;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');

        try {
            $jobInstance = $this->jobRepository->getJobInstanceById($id);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This instance no longer exists.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/');
        }

        $this->coreRegistry->register(RegistryConstants::JOB_INSTANCE, $jobInstance);
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend($jobInstance->getCode());

        return $resultPage;
    }
}