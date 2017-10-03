<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 03.10.17
 */

namespace Dopamedia\Batch\Console\Command;

use Dopamedia\Batch\Model\JobInstance;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Dopamedia\Batch\Model\ResourceModel\JobInstance\Collection as JobInstanceCollection;
use Symfony\Component\Console\Helper\TableFactory;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class JobInstanceListCommand
 * @package Dopamedia\Batch\Console\Command
 */
class JobInstanceListCommand extends Command
{
    /**
     * @var JobInstanceCollection
     */
    private $jobInstanceCollection;

    /**
     * @var TableFactory
     */
    private $tableFactory;

    /**
     * BatchJobListCommand constructor.
     * @param JobInstanceCollection $jobInstanceCollection
     * @param TableFactory $tableFactory
     */
    public function __construct(
        JobInstanceCollection $jobInstanceCollection,
        TableFactory $tableFactory
    )
    {
        $this->jobInstanceCollection = $jobInstanceCollection;
        $this->tableFactory = $tableFactory;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('batch:job-instance:list')
            ->setDescription('List the existing job instances');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->jobInstanceCollection->count() === 0) {
            $output->writeln('<comment>No JobInstances defined</comment>');
            return Cli::RETURN_FAILURE;
        } else {
            $this->buildTable($output)->render();
            return Cli::RETURN_SUCCESS;
        }
    }

    /**
     * @param OutputInterface $output
     * @return Table
     */
    private function buildTable(OutputInterface $output): Table
    {
        $table = $this->tableFactory->create(['output' => $output]);

        $table->setHeaders(['id', 'code', 'job name']);

        /** @var JobInstance $jobInstance */
        foreach ($this->jobInstanceCollection->getItems() as $jobInstance) {
            $table->addRow([
                $jobInstance->getId(),
                $jobInstance->getCode(),
                $jobInstance->getJobName()
            ]);
        }

        return $table;
    }
}