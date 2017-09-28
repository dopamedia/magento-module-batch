<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 28.09.17
 */

namespace Dopamedia\Batch\Model\ResourceModel\JobExecution;

use Dopamedia\Batch\Model\JobExecution;
use Dopamedia\Batch\Model\ResourceModel\StepExecution as ResourceStepExecution;
use Dopamedia\Batch\Model\StepExecution;
use Dopamedia\PhpBatch\JobInstanceInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Dopamedia\Batch\Model\ResourceModel\JobExecution
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
     * @param JobInstanceInterface $jobInstance
     * @return Collection
     */
    public function setJobInstanceFilter(JobInstanceInterface $jobInstance): Collection
    {
        $this->addFieldToFilter(JobExecution::JOB_INSTANCE_ID, $jobInstance->getId());

        return $this;
    }
}