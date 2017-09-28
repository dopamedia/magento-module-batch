<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 28.09.17
 */

namespace Dopamedia\Batch\Model;

use Dopamedia\Batch\Api\JobInstanceRepositoryInterface;
use Dopamedia\PhpBatch\JobInstanceInterface;
use Dopamedia\Batch\Model\ResourceModel\JobInstance as ResourceJobInstance;
use Dopamedia\Batch\Model\JobInstanceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;

/**
 * Class JobInstanceRepository
 * @package Dopamedia\Batch\Model
 */
class JobInstanceRepository implements JobInstanceRepositoryInterface
{
    /**
     * @var ResourceJobInstance
     */
    private $resource;
    /**
     * @var \Dopamedia\Batch\Model\JobInstanceFactory
     */
    private $jobInstanceFactory;

    /**
     * JobInstanceRepository constructor.
     * @param ResourceJobInstance $resource
     * @param \Dopamedia\Batch\Model\JobInstanceFactory $jobInstanceFactory
     */
    public function __construct(
        ResourceJobInstance $resource,
        JobInstanceFactory $jobInstanceFactory
    )
    {
        $this->resource = $resource;
        $this->jobInstanceFactory = $jobInstanceFactory;
    }

    /**
     * @param int $jobInstanceId
     * @return JobInstanceInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $jobInstanceId): JobInstanceInterface
    {
        /** @var JobInstance $jobInstance */
        $jobInstance = $this->jobInstanceFactory->create();
        $this->resource->load($jobInstance, $jobInstanceId);
        if ($jobInstance->getId() === null) {
            throw new NoSuchEntityException(
                new Phrase('JobInstance with id "%1" does not exist.', [$jobInstanceId])
            );
        }

        return $jobInstance;
    }

}