<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 08.10.17
 */

namespace Dopamedia\Batch\Model\ResourceModel\JobExecution\Grid;

use Dopamedia\Batch\Model\ResourceModel\JobExecution as ResourceJobExecution;
use Magento\Customer\Ui\Component\DataProvider\Document;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class Collection
 * @package Dopamedia\Batch\Model\ResourceModel\JobExecution\Grid
 */
class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @inheritdoc
     */
    protected $document = Document::class;

    /**
     * @inheritdoc
     */
    protected $_map = ['fields' => ['id' => 'main_table.id']];

    /**
     * Collection constructor.
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'batch_job_execution',
        $resourceModel = ResourceJobExecution::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * @inheritDoc
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()
            ->joinInner(
                ['bji' => $this->getTable('batch_job_instance')],
                'main_table.job_instance_id = bji.id')
            ->joinLeft(
                ['bse' => $this->getTable('batch_step_execution')],
                'main_table.id = bse.job_execution_id')
            ->joinLeft(
                ['bw' => $this->getTable('batch_warning')],
                'bse.id = bw.step_execution_id')
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(
                [
                    'main_table.*',
                    'code' => 'bji.code',
                    'warning_count' => new \Zend_Db_Expr('COUNT(bw.id)')
                ])
            ->group('main_table.id');
    }
}
