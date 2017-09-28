<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 27.09.17
 */

namespace Dopamedia\Batch\Api;

use Dopamedia\PhpBatch\JobInstanceInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface JobInstanceRepositoryInterface
 * @package Dopamedia\Batch\Api
 */
interface JobInstanceRepositoryInterface
{
    /**
     * @param int $jobInstanceId
     * @return JobInstanceInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $jobInstanceId): JobInstanceInterface;

    /**
     * @param string $jobInstanceCode
     * @return JobInstanceInterface
     * @throws NoSuchEntityException
     */
    public function getByCode(string $jobInstanceCode): JobInstanceInterface;

    /**
     * @param JobInstanceInterface $jobInstance
     * @return JobInstanceInterface
     * @throws CouldNotSaveException
     */
    public function save(JobInstanceInterface $jobInstance): JobInstanceInterface;
}