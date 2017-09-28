<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 28.09.17
 */

namespace Dopamedia\Batch\Model\ResourceModel\StepExecution;

use Dopamedia\Batch\Model\ResourceModel\StepExecution as ResourceStepExecution;
use Dopamedia\Batch\Model\StepExecution;
use Dopamedia\PhpBatch\JobExecutionInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Dopamedia\Batch\Model\ResourceModel\StepExecution
 */
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(StepExecution::class, ResourceStepExecution::class);
    }

    /**
     * @param JobExecutionInterface $jobExecution
     * @return Collection
     */
    public function setJobExecutionFilter(JobExecutionInterface $jobExecution): Collection
    {
        $this->addFieldToFilter(StepExecution::JOB_EXECUTION_ID, $jobExecution->getId());

        return $this;
    }
}