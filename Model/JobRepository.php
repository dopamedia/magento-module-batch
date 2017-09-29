<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 29.09.17
 */

namespace Dopamedia\Batch\Model;

use Dopamedia\Batch\Api\JobExecutionRepositoryInterface;
use Dopamedia\Batch\Api\JobInstanceRepositoryInterface;
use Dopamedia\Batch\Api\StepExecutionRepositoryInterface;
use Dopamedia\Batch\Api\WarningRepositoryInterface;
use Dopamedia\PhpBatch\Job\JobParameters;
use Dopamedia\PhpBatch\JobExecutionInterfaceFactory;
use Dopamedia\PhpBatch\JobExecutionInterface;
use Dopamedia\PhpBatch\JobInstanceInterface;
use Dopamedia\PhpBatch\Repository\JobRepositoryInterface;
use Dopamedia\PhpBatch\StepExecutionInterface;
use Dopamedia\PhpBatch\WarningInterface;
use Dopamedia\PhpBatch\WarningInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class JobRepository
 * @package Dopamedia\Batch\Model
 */
class JobRepository implements JobRepositoryInterface
{
    /**
     * @var JobExecutionRepositoryInterface
     */
    private $jobExecutionRepository;

    /**
     * @var JobInstanceRepositoryInterface
     */
    private $jobInstanceRepository;

    /**
     * @var StepExecutionRepositoryInterface
     */
    private $stepExecutionRepository;

    /**
     * @var WarningRepositoryInterface
     */
    private $warningRepository;

    /**
     * @var JobExecutionInterfaceFactory
     */
    private $jobExecutionFactory;

    /**
     * @var WarningInterfaceFactory
     */
    private $warningFactory;

    /**
     * JobRepository constructor.
     * @param JobExecutionRepositoryInterface $jobExecutionRepository
     * @param JobInstanceRepositoryInterface $jobInstanceRepository
     * @param StepExecutionRepositoryInterface $stepExecutionRepository
     * @param WarningRepositoryInterface $warningRepository
     * @param JobExecutionInterfaceFactory $jobExecutionFactory
     * @param WarningInterfaceFactory $warningFactory
     */
    public function __construct(
        JobExecutionRepositoryInterface $jobExecutionRepository,
        JobInstanceRepositoryInterface $jobInstanceRepository,
        StepExecutionRepositoryInterface $stepExecutionRepository,
        WarningRepositoryInterface $warningRepository,
        JobExecutionInterfaceFactory $jobExecutionFactory,
        WarningInterfaceFactory $warningFactory
    )
    {
        $this->jobExecutionRepository = $jobExecutionRepository;
        $this->stepExecutionRepository = $stepExecutionRepository;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->warningRepository = $warningRepository;
        $this->jobExecutionFactory = $jobExecutionFactory;
        $this->warningFactory = $warningFactory;
    }

    /**
     * @param JobInstanceInterface $jobInstance
     * @param JobParameters $jobParameters
     * @return JobExecutionInterface
     * @throws CouldNotSaveException
     */
    public function createJobExecution(JobInstanceInterface $jobInstance, JobParameters $jobParameters): JobExecutionInterface
    {
        $this->jobInstanceRepository->save($jobInstance);

        /** @var JobExecutionInterface $jobExecution */
        $jobExecution = $this->jobExecutionFactory->create()
            ->setJobInstance($jobInstance)
            ->setJobParameters($jobParameters);

        $this->jobExecutionRepository->save($jobExecution);

        return $jobExecution;
    }

    /**
     * @param JobExecutionInterface $jobExecution
     * @throws CouldNotSaveException
     */
    public function updateJobExecution(JobExecutionInterface $jobExecution): void
    {
        $this->jobExecutionRepository->save($jobExecution);
    }

    /**
     * @param StepExecutionInterface $stepExecution
     * @throws CouldNotSaveException
     */
    public function updateStepExecution(StepExecutionInterface $stepExecution): void
    {
        $this->stepExecutionRepository->save($stepExecution);
    }

    /**
     * @param StepExecutionInterface $stepExecution
     * @param string $reason
     * @param array $reasonParameters
     * @param array $item
     * @return WarningInterface
     * @throws CouldNotSaveException
     */
    public function createWarning(
        StepExecutionInterface $stepExecution,
        string $reason,
        array $reasonParameters = [],
        array $item = []
    ): WarningInterface
    {
        /** @var WarningInterface $warning */
        $warning = $this->warningFactory->create()
            ->setStepExecution($stepExecution)
            ->setReason($reason)
            ->setReasonParameters($reasonParameters)
            ->setItem($item);

        $this->warningRepository->save($warning);

        return $warning;
    }
}