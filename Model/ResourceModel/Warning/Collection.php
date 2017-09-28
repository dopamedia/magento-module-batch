<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 28.09.17
 */

namespace Dopamedia\Batch\Model\ResourceModel\Warning;

use Dopamedia\Batch\Model\ResourceModel\Warning as ResourceWarning;
use Dopamedia\Batch\Model\Warning;
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
}