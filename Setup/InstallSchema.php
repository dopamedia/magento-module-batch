<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 28.09.17
 */

namespace Dopamedia\Batch\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'batch_job_instance'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('batch_job_instance')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
        )->addColumn(
            'code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            ['nullable' => false]
        )->addColumn(
            'job_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11
        )->addColumn(
            'raw_parameters',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M'
        );

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'batch_job_execution'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('batch_job_execution')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
        )->addColumn(
            'pid',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => true]
        )->addColumn(
            'user',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true]
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null
        )->addColumn(
            'start_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true]
        )->addColumn(
            'end_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true]
        )->addColumn(
            'create_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true]
        )->addColumn(
            'updated_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true]
        )->addColumn(
            'exit_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true]
        )->addColumn(
            'exit_description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            ['nullable' => true]
        )->addColumn(
            'failure_exceptions',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            ['nullable' => true]
        )->addColumn(
            'log_file',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true]
        )->addColumn(
            'job_instance_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false]
        )->addIndex(
            $installer->getIdxName('batch_job_execution', 'job_instance_id'),
            'job_instance_id'
        )->addForeignKey(
            $installer->getFkName('batch_job_execution', 'job_instance_id', 'batch_job_instance', 'id'),
            'job_instance_id',
            $installer->getTable('batch_job_instance'),
            'id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'batch_step_execution'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('batch_step_execution')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
        )->addColumn(
            'step_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            ['nullable' => true]
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
        )->addColumn(
            'read_count',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
        )->addColumn(
            'write_count',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
        )->addColumn(
            'filter_count',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
        )->addColumn(
            'start_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true]
        )->addColumn(
            'end_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true]
        )->addColumn(
            'exit_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true]
        )->addColumn(
            'exit_description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            ['nullable' => true]
        )->addColumn(
            'terminate_only',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => true]
        )->addColumn(
            'failure_exceptions',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            ['nullable' => true]
        )->addColumn(
            'errors',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M'
        )->addColumn(
            'summary',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M'
        )->addColumn(
            'job_execution_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false]
        )->addIndex(
            $installer->getIdxName('batch_step_execution', 'job_execution_id'),
            'job_execution_id'
        )->addForeignKey(
            $installer->getFkName('batch_step_execution', 'job_execution_id', 'batch_job_execution', 'id'),
            'job_execution_id',
            $installer->getTable('batch_job_execution'),
            'id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'batch_warning'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('batch_warning')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
        )->addColumn(
            'reason',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            ['nullable' => true]
        )->addColumn(
            'reason_parameters',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            ['nullable' => false]
        )->addColumn(
            'item',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            ['nullable' => false]
        )->addColumn(
            'step_execution_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false]
        )->addIndex(
            $installer->getIdxName('batch_warning', 'step_execution_id'),
            'step_execution_id'
        )->addForeignKey(
            $installer->getFkName('batch_warning', 'step_execution_id', 'batch_step_execution', 'id'),
            'step_execution_id',
            $installer->getTable('batch_step_execution'),
            'id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $installer->getConnection()->createTable($table);
    }
}