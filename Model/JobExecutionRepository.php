<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 28.09.17
 */

namespace Dopamedia\Batch\Model;

use Dopamedia\Batch\Api\JobExecutionRepositoryInterface;
use Dopamedia\Batch\Model\ResourceModel\JobExecution as ResourceJobExecution;
use Dopamedia\PhpBatch\JobExecutionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;

/**
 * Class JobExecutionRepository
 * @package Dopamedia\Batch\Model
 */
class JobExecutionRepository implements JobExecutionRepositoryInterface
{
    /**
     * @var ResourceJobExecution
     */
    private $resource;

    /**
     * @var JobExecutionFactory
     */
    private $jobExecutionFactory;

    /**
     * JobExecutionRepository constructor.
     * @param ResourceJobExecution $resource
     */
    public function __construct(
        ResourceJobExecution $resource,
        JobExecutionFactory $jobExecutionFactory
    )
    {
        $this->resource = $resource;
        $this->jobExecutionFactory = $jobExecutionFactory;
    }

    /**
     * @param int $jobExecutionId
     * @return JobExecutionInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $jobExecutionId): JobExecutionInterface
    {
        /** @var JobExecution $jobExecution */
        $jobExecution = $this->jobExecutionFactory->create();
        $this->resource->load($jobExecution, $jobExecutionId);
        if ($jobExecution->getId() === null) {
            throw new NoSuchEntityException(
                new Phrase('JobExecution with id "%1" does not exist.', [$jobExecutionId])
            );
        }

        return $jobExecution;
    }
}