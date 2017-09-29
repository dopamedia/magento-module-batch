<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 27.09.17
 */

namespace Dopamedia\Batch\Api;

use Dopamedia\PhpBatch\JobExecutionInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface JobExecutionRepositoryInterface
 * @package Dopamedia\Batch\Api
 */
interface JobExecutionRepositoryInterface
{
    /**
     * @param int $jobExecutionId
     * @return JobExecutionInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $jobExecutionId): JobExecutionInterface;

    /**
     * @param JobExecutionInterface $jobExecution
     * @return JobExecutionInterface
     * @throws CouldNotSaveException
     */
    public function save(JobExecutionInterface $jobExecution): JobExecutionInterface;

}