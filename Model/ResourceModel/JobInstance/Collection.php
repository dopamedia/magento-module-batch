<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 28.09.17
 */

namespace Dopamedia\Batch\Model\ResourceModel\JobInstance;

use Dopamedia\Batch\Model\JobInstance;
use Dopamedia\Batch\Model\ResourceModel\JobInstance as ResourceJobInstance;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Dopamedia\Batch\Model\ResourceModel\JobInstance
 */
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(JobInstance::class, ResourceJobInstance::class);
    }
}