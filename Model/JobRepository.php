<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 29.09.17
 */

namespace Dopamedia\Batch\Model;

use Dopamedia\Batch\Model\ResourceModel\JobInstance\Collection as JobInstanceCollection;
use Dopamedia\PhpBatch\Job\JobParameters;
use Dopamedia\PhpBatch\JobExecutionInterface;
use Dopamedia\PhpBatch\JobInstanceInterface;
use Dopamedia\PhpBatch\Repository\JobRepositoryInterface;
use Dopamedia\PhpBatch\StepExecutionInterface;
use Dopamedia\PhpBatch\WarningInterface;
use Dopamedia\Batch\Model\ResourceModel\JobExecution as ResourceJobExecution;
use Dopamedia\Batch\Model\ResourceModel\JobInstance as ResourceJobInstance;
use Dopamedia\Batch\Model\ResourceModel\StepExecution as ResourceStepExecution;
use Dopamedia\Batch\Model\ResourceModel\Warning as ResourceWarning;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Phrase;

/**
 * Class JobRepository
 * @package Dopamedia\Batch\Model
 */
class JobRepository implements JobRepositoryInterface
{
    /**
     * @var ResourceJobExecution
     */
    private $resourceJobExecution;

    /**
     * @var JobExecutionFactory
     */
    private $jobExecutionFactory;

    /**
     * @var ResourceJobInstance
     */
    private $resourceJobInstance;

    /**
     * @var JobInstanceFactory
     */
    private $jobInstanceFactory;

    /**
     * @var JobInstanceCollection
     */
    private $jobInstanceCollection;

    /**
     * @var ResourceStepExecution
     */
    private $resourceStepExecution;

    /**
     * @var StepExecutionFactory
     */
    private $stepExecutionFactory;

    /**
     * @var ResourceWarning
     */
    private $resourceWarning;

    /**
     * @var WarningFactory
     */
    private $warningFactory;

    /**
     * JobRepository constructor.
     * @param ResourceJobExecution $resourceJobExecution
     * @param JobExecutionFactory $jobExecutionFactory
     * @param ResourceJobInstance $resourceJobInstance
     * @param JobInstanceFactory $jobInstanceFactory
     * @param JobInstanceCollection $jobInstanceCollection
     * @param ResourceStepExecution $resourceStepExecution
     * @param StepExecutionFactory $stepExecutionFactory
     * @param ResourceWarning $resourceWarning
     * @param WarningFactory $warningFactory
     */
    public function __construct(
        ResourceJobExecution $resourceJobExecution,
        JobExecutionFactory $jobExecutionFactory,
        ResourceJobInstance $resourceJobInstance,
        JobInstanceFactory $jobInstanceFactory,
        JobInstanceCollection $jobInstanceCollection,
        ResourceStepExecution $resourceStepExecution,
        StepExecutionFactory $stepExecutionFactory,
        ResourceWarning $resourceWarning,
        WarningFactory $warningFactory
    )
    {
        $this->resourceJobExecution = $resourceJobExecution;
        $this->jobExecutionFactory = $jobExecutionFactory;
        $this->resourceJobInstance = $resourceJobInstance;
        $this->jobInstanceFactory = $jobInstanceFactory;
        $this->jobInstanceCollection = $jobInstanceCollection;
        $this->resourceStepExecution = $resourceStepExecution;
        $this->stepExecutionFactory = $stepExecutionFactory;
        $this->resourceWarning = $resourceWarning;
        $this->warningFactory = $warningFactory;
    }

    /**
     * @param int $id
     * @return JobExecutionInterface
     * @throws NoSuchEntityException
     */
    public function getJobExecutionById(int $id): JobExecutionInterface
    {
        /** @var JobExecution $jobExecution */
        $jobExecution = $this->jobExecutionFactory->create();
        $this->resourceJobExecution->load($jobExecution, $id);

        if ($jobExecution->getId() === null) {
            throw new NoSuchEntityException(
                new Phrase('JobExecution with id "%1" does not exist.', [$id])
            );
        }

        return $jobExecution;
    }

    /**
     * @param JobInstanceInterface|AbstractModel $jobInstance
     * @param JobParameters $jobParameters
     * @return JobExecutionInterface
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function createJobExecution(JobInstanceInterface $jobInstance, JobParameters $jobParameters): JobExecutionInterface
    {
        $this->resourceJobInstance->save($jobInstance);

        /** @var JobExecution $jobExecution */
        $jobExecution = $this->jobExecutionFactory->create()
            ->setJobInstance($jobInstance)
            ->setJobParameters($jobParameters);

        $this->resourceJobExecution->save($jobExecution);

        return $jobExecution;
    }

    /**
     * @param JobExecutionInterface|AbstractModel $jobExecution
     * @return JobExecutionInterface
     * @throws CouldNotSaveException
     */
    public function saveJobExecution(JobExecutionInterface $jobExecution): JobExecutionInterface
    {
        try {
            $this->resourceJobExecution->save($jobExecution);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }

        return $jobExecution;
    }

    /**
     * @param int $id
     * @return JobInstanceInterface
     * @throws NoSuchEntityException
     */
    public function getJobInstanceById(int $id): JobInstanceInterface
    {
        /** @var JobInstance $jobInstance */
        $jobInstance = $this->jobInstanceFactory->create();
        $this->resourceJobInstance->load($jobInstance, $id);

        if ($jobInstance->getId() === null) {
            throw new NoSuchEntityException(
                new Phrase('JobInstance with id "%1" does not exist.', [$id])
            );
        }

        return $jobInstance;
    }

    /**
     * @param string $code
     * @return JobInstanceInterface
     * @throws NoSuchEntityException
     */
    public function getJobInstanceByCode(string $code): JobInstanceInterface
    {
        /** @var JobInstance $jobInstance */
        $jobInstance = $this->jobInstanceFactory->create();
        $this->resourceJobInstance->load($jobInstance, $code, JobInstance::CODE);

        if ($jobInstance->getId() === null) {
            throw new NoSuchEntityException(
                new Phrase('JobInstance with code "%1" does not exist.', [$code])
            );
        }

        return $jobInstance;
    }

    /**
     * @inheritDoc
     */
    public function getJobInstances(): array
    {
        return $this->jobInstanceCollection->getItems();
    }

    /**
     * @param JobInstanceInterface|AbstractModel $jobInstance
     * @return JobInstanceInterface
     * @throws CouldNotSaveException
     */
    public function saveJobInstance(JobInstanceInterface $jobInstance): JobInstanceInterface
    {
        try {
            $this->resourceJobInstance->save($jobInstance);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }

        return $jobInstance;
    }

    /**
     * @param int $id
     * @return StepExecutionInterface
     * @throws NoSuchEntityException
     */
    public function getStepExecutionById(int $id): StepExecutionInterface
    {
        /** @var StepExecution $stepExecution */
        $stepExecution = $this->stepExecutionFactory->create();
        $this->resourceStepExecution->load($stepExecution, $id);

        if ($stepExecution->getId() === null) {
            throw new NoSuchEntityException(
                new Phrase('StepExecution with id "%1" does not exist.', [$id])
            );
        }

        return $stepExecution;
    }

    /**
     * @param StepExecutionInterface|AbstractModel $stepExecution
     * @return StepExecutionInterface
     * @throws CouldNotSaveException
     */
    public function saveStepExecution(StepExecutionInterface $stepExecution): StepExecutionInterface
    {
        try {
            $this->resourceStepExecution->save($stepExecution);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }

        return $stepExecution;
    }

    /**
     * @param int $id
     * @return WarningInterface
     * @throws NoSuchEntityException
     */
    public function getWarningById(int $id): WarningInterface
    {
        /** @var Warning $waring */
        $waring = $this->warningFactory->create();
        $this->resourceWarning->load($waring, $id);

        if ($waring->getId() === null) {
            throw new NoSuchEntityException(
                new Phrase('Warning with id "%1" does not exist.', [$id])
            );
        }

        return $waring;
    }

    /**
     * @param StepExecutionInterface $stepExecution
     * @param string $reason
     * @param array $reasonParameters
     * @param array $item
     * @return WarningInterface
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function createWarning(
        StepExecutionInterface $stepExecution,
        string $reason,
        array $reasonParameters = [],
        array $item = []
    ): WarningInterface
    {
        /** @var Warning $warning */
        $warning = $this->warningFactory->create()
            ->setStepExecution($stepExecution)
            ->setReason($reason)
            ->setReasonParameters($reasonParameters)
            ->setItem($item);

        $this->resourceWarning->save($warning);

        return $warning;
    }

    /**
     * @param WarningInterface|AbstractModel $warning
     * @return WarningInterface
     * @throws CouldNotSaveException
     */
    public function saveWarning(WarningInterface $warning): WarningInterface
    {
        try {
            $this->resourceWarning->save($warning);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }

        return $warning;
    }
}