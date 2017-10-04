<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 28.09.17
 */

namespace Dopamedia\Batch\Job;

use Dopamedia\PhpBatch\Job\JobRegistryInterface;
use Dopamedia\PhpBatch\JobInterface;
use Dopamedia\PhpBatch\Job\UndefinedJobException;
use Magento\Framework\Phrase;

/**
 * Class JobRegistry
 * @package Dopamedia\Batch\Job
 */
class JobRegistry implements JobRegistryInterface
{
    /**
     * @var array|JobInterface[]
     */
    private $jobs;

    /**
     * JobRegistry constructor.
     * @param JobInterface[]|array $jobs
     */
    public function __construct(array $jobs = [])
    {
        $this->jobs = $jobs;
    }

    /**
     * @inheritDoc
     */
    public function getJob(string $jobName): JobInterface
    {
        foreach ($this->jobs as $job) {
            if ($job->getName() === $jobName) {
                return $job;
            }
        }

        throw new UndefinedJobException(
            new Phrase('The job "%1" is not registered', [$jobName])
        );
    }

    /**
     * @inheritDoc
     */
    public function getJobs(): array
    {
        return $this->jobs;
    }

}