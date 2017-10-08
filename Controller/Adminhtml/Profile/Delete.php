<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 08.10.17
 */

namespace Dopamedia\Batch\Controller\Adminhtml\Profile;

use Dopamedia\Batch\Controller\Adminhtml\Profile;
use Dopamedia\Batch\Model\JobRepository;
use Dopamedia\PhpBatch\Repository\JobRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Delete
 * @package Dopamedia\Batch\Controller\Adminhtml\Profile
 */
class Delete extends Profile
{
    /**
     * @var JobRepositoryInterface|JobRepository
     */
    private $jobRepository;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param JobRepositoryInterface $jobRepository
     */
    public function __construct(
        Action\Context $context,
        JobRepositoryInterface $jobRepository
    )
    {
        parent::__construct($context);
        $this->jobRepository = $jobRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam('id');

        if ($id !== null) {
            try {
                $jobInstance = $this->jobRepository->getJobInstanceById($id);
                $this->jobRepository->deleteJobInstance($jobInstance);

                $this->messageManager->addSuccessMessage(__('You deleted the profile.'));
                return $resultRedirect->setPath('*/*/');
            } catch (NoSuchEntityException | CouldNotSaveException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find a profile to delete.'));

        return $resultRedirect->setPath('*/*/');
    }
}