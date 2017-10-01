<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 28.09.17
 */

namespace Dopamedia\Batch\Model\ResourceModel\Warning;

use Dopamedia\Batch\Model\ResourceModel\Warning as ResourceWarning;
use Dopamedia\Batch\Model\Warning;
use Dopamedia\PhpBatch\StepExecutionInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Dopamedia\Batch\Model\ResourceModel\Warning
 */
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(Warning::class, ResourceWarning::class);
    }

    /**
     * @param StepExecutionInterface $stepExecution
     * @return Collection
     */
    public function setStepExecutionFilter(StepExecutionInterface $stepExecution): Collection
    {
        $this->addFieldToFilter(Warning::STEP_EXECUTION_ID, $stepExecution->getId());

        return $this;
    }
}