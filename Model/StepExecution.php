<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 27.09.17
 */

namespace Dopamedia\Batch\Model;

use Dopamedia\Batch\Api\JobExecutionRepositoryInterface;
use Dopamedia\Batch\Model\ResourceModel\StepExecution as ResouceStepExecution;
use Dopamedia\PhpBatch\BatchStatus;
use Dopamedia\PhpBatch\ExitStatus;
use Dopamedia\PhpBatch\Item\ExecutionContext;
use Dopamedia\PhpBatch\Item\InvalidItemInterface;
use Dopamedia\PhpBatch\Job\RuntimeErrorException;
use Dopamedia\PhpBatch\JobExecutionInterface;
use Dopamedia\PhpBatch\Job\JobParameters;
use Dopamedia\PhpBatch\StepExecutionInterface;
use Dopamedia\Batch\Model\ResourceModel\Warning\Collection as WarningCollection;
use Dopamedia\Batch\Model\ResourceModel\Warning\CollectionFactory as WarningCollectionFactory;
use Dopamedia\PhpBatch\WarningInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Serialize\Serializer\Json as Serializer;

/**
 * Class StepExecution
 * @package Dopamedia\Batch\Model
 */
class StepExecution extends AbstractModel implements StepExecutionInterface
{
    /**#@+*/
    public const ID = 'id';
    public const STEP_NAME = 'step_name';
    public const STATUS = 'status';
    public const READ_COUNT = 'read_count';
    public const WRITE_COUNT = 'write_count';
    public const FILTER_COUNT = 'filter_count';
    public const START_TIME = 'start_time';
    public const END_TIME = 'end_time';
    public const EXIT_CODE = 'exit_code';
    public const EXIT_DESCRIPTION = 'exit_description';
    public const TERMINATE_ONLY = 'terminate_only';
    public const FAILURE_EXCEPTIONS = 'failure_exceptions';
    public const ERRORS = 'errors';
    public const SUMMARY = 'summary';
    public const JOB_EXECUTION_ID = 'job_execution_id';
    /**#@-*/

    /**
     * @var JobExecutionRepositoryInterface
     */
    private $jobExecutionRepository;

    /**
     * @var null|JobExecutionInterface
     */
    private $jobExecution;

    /**
     * @var null|ExecutionContext
     */
    private $executionContext;

    /**
     * @var null|ExitStatus
     */
    private $exitStatus;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var WarningCollectionFactory
     */
    private $warningCollectionFactory;

    /**
     * @var WarningFactory
     */
    private $warningFactory;

    /**
     * @var null|WarningCollection
     */
    private $warningCollection;

    /**
     * StepExecution constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param JobExecutionRepositoryInterface $jobExecutionRepository
     * @param Serializer $serializer
     * @param WarningCollectionFactory $warningCollectionFactory
     * @param WarningFactory $warningFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        JobExecutionRepositoryInterface $jobExecutionRepository,
        Serializer $serializer,
        WarningCollectionFactory $warningCollectionFactory,
        WarningFactory $warningFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->jobExecutionRepository = $jobExecutionRepository;
        $this->serializer = $serializer;
        $this->warningCollectionFactory = $warningCollectionFactory;
        $this->warningFactory = $warningFactory;
        $this->setStartTime(new \DateTime());
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ResouceStepExecution::class);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function setId($id)
    {
        return $this->setData(self::ID);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function getStepName(): ?string
    {
        return $this->getData(self::STEP_NAME);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function setStepName(string $stepName): StepExecutionInterface
    {
        return $this->setData(self::STEP_NAME, $stepName);
    }

    /**
     * @return int|null
     * @codeCoverageIgnore
     */
    public function getJobExecutionId(): ?int
    {
        return $this->getData(self::JOB_EXECUTION_ID);
    }

    /**
     * @param int $jobExecutionId
     * @return StepExecutionInterface
     * @codeCoverageIgnore
     */
    public function setJobExecutionId(int $jobExecutionId): StepExecutionInterface
    {
        return $this->setData(self::JOB_EXECUTION_ID, $jobExecutionId);
    }

    /**
     * @return JobExecutionInterface|null
     * @throws NoSuchEntityException
     */
    public function getJobExecution(): ?JobExecutionInterface
    {
        if ($this->jobExecution === null && $this->getJobExecutionId() !== null) {
            $this->jobExecution = $this->jobExecutionRepository->getById($this->getJobExecutionId());
        }

        return $this->jobExecution;
    }

    /**
     * @inheritDoc
     */
    public function setJobExecution(JobExecutionInterface $jobExecution): StepExecutionInterface
    {
        $this->jobExecution = $jobExecution;
        $this->setJobExecutionId($jobExecution->getId());

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getExecutionContext(): ExecutionContext
    {
        if ($this->executionContext === null) {
            $this->executionContext = new ExecutionContext();
        }

        return $this->executionContext;
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function setExecutionContext(ExecutionContext $executionContext): StepExecutionInterface
    {
        $this->executionContext = $executionContext;

        return $this;
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function getEndTime(): ?\DateTime
    {
        return $this->getData(self::END_TIME);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function setEndTime(\DateTime $endTime): StepExecutionInterface
    {
        return $this->setData(self::END_TIME, $endTime);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function getReadCount(): ?int
    {
        return $this->getData(self::READ_COUNT);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function setReadCount(int $readCount): StepExecutionInterface
    {
        return $this->setData(self::READ_COUNT);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function getWriteCount(): ?int
    {
        return $this->getData(self::WRITE_COUNT);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function setWriteCount(int $writeCount): StepExecutionInterface
    {
        return $this->getData(self::WRITE_COUNT);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function getFilterCount(): ?int
    {
        return $this->getData(self::FILTER_COUNT);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function setFilterCount(int $filterCount): StepExecutionInterface
    {
        return $this->setData(self::FILTER_COUNT);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function getStartTime(): ?\DateTime
    {
        return $this->getData(self::START_TIME);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function setStartTime(\DateTime $startTime): StepExecutionInterface
    {
        return $this->setData(self::START_TIME, $startTime);
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): BatchStatus
    {
        if ($this->getData(self::STATUS) instanceof BatchStatus) {
            return $this->getData(self::STATUS);
        } elseif (is_int($this->getData(self::STATUS))) {
            return new BatchStatus($this->getData(self::STATUS));
        }

        return BatchStatus::STARTING();
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function setStatus(BatchStatus $batchStatus): StepExecutionInterface
    {
        return $this->setData(self::STATUS, $batchStatus);
    }

    /**
     * @inheritDoc
     */
    public function upgradeStatus(BatchStatus $batchStatus): StepExecutionInterface
    {
        $newBatchStatus = $this->getStatus();
        $newBatchStatus->upgradeTo($batchStatus);
        $this->setStatus($newBatchStatus);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setExitStatus(ExitStatus $exitStatus): StepExecutionInterface
    {
        $this->exitStatus = $exitStatus;
        $this->setData(self::EXIT_CODE, $exitStatus->getExitCode());
        $this->setData(self::EXIT_DESCRIPTION, $exitStatus->getExitDescription());

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getExitStatus(): ExitStatus
    {
        if ($this->exitStatus === null) {
            $this->exitStatus = new ExitStatus(ExitStatus::EXECUTING);
        }

        return $this->exitStatus;
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function isTerminateOnly(): bool
    {
        return $this->getData(self::TERMINATE_ONLY) ?? false;
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function setTerminateOnly(): StepExecutionInterface
    {
        return $this->setData(self::TERMINATE_ONLY, true);
    }

    /**
     * @return JobParameters
     * @throws NoSuchEntityException
     */
    public function getJobParameters(): JobParameters
    {
        return $this->getJobExecution()->getJobParameters();
    }

    /**
     * @inheritDoc
     */
    public function getFailureExceptions(): array
    {
        if (is_string($this->getData(self::FAILURE_EXCEPTIONS))) {
            return $this->serializer->unserialize($this->getData(self::FAILURE_EXCEPTIONS));
        } elseif ($this->getData(self::FAILURE_EXCEPTIONS) === null) {
            return [];
        }

        return is_array($this->getData(self::FAILURE_EXCEPTIONS)) ? $this->getData(self::FAILURE_EXCEPTIONS) : [];
    }

    /**
     * @inheritDoc
     */
    public function addFailureException(\Throwable $throwable): StepExecutionInterface
    {
        $failureExceptions = $this->getFailureExceptions();

        $failureExceptions[] = [
            'class' => get_class($throwable),
            'message' => $throwable->getMessage(),
            'messageParameters' => $throwable instanceof RuntimeErrorException ? $throwable->getMessageParameters() : [],
            'code' => $throwable->getCode(),
            'trace' => $throwable->getTraceAsString()
        ];

        return $this->setData(self::FAILURE_EXCEPTIONS, $failureExceptions);
    }

    /**
     * @inheritDoc
     */
    public function getErrors(): array
    {
        if (is_string($this->getData(self::ERRORS))) {
            return $this->serializer->unserialize($this->getData(self::ERRORS));
        } elseif ($this->getData(self::ERRORS) === null) {
            return [];
        }

        return is_array($this->getData(self::ERRORS)) ? $this->getData(self::ERRORS) : [];
    }

    /**
     * @inheritDoc
     */
    public function addError(string $message): StepExecutionInterface
    {
        $errors = $this->getErrors();

        $errors[] = $message;

        return $this->setData(self::ERRORS, $errors);
    }

    /**
     * @return WarningCollection
     */
    private function getWarningCollection(): WarningCollection
    {
        if ($this->warningCollection === null) {
            $this->warningCollection = $this->warningCollectionFactory->create()
                ->setStepExecutionFilter($this);
        }

        return $this->warningCollection;
    }

    /**
     * @inheritDoc
     */
    public function getWarnings(): array
    {
        return $this->getWarningCollection()->getItems();
    }

    /**
     * @param string $reason
     * @param array $reasonParameters
     * @param InvalidItemInterface $item
     * @return StepExecutionInterface
     * @throws \Exception
     */
    public function addWarning(
        string $reason,
        array $reasonParameters,
        InvalidItemInterface $item
    ): StepExecutionInterface
    {
        $data = $item->getInvalidData() ?? [];

        /** @var WarningInterface|DataObject $warning */
        $warning = $this->warningFactory->create()
            ->setReason($reason)
            ->setReasonParameters($reasonParameters)
            ->setItem($data)
            ->setStepExecution($this);

        $this->getWarningCollection()->addItem($warning)->save();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSummary(): array
    {
        if (is_string($this->getData(self::SUMMARY))) {
            return $this->serializer->unserialize($this->getData(self::SUMMARY));
        } elseif ($this->getData(self::SUMMARY) === null) {
            return [];
        }

        return is_array($this->getData(self::SUMMARY)) ? $this->getData(self::SUMMARY) : [];
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function setSummary(array $summary): StepExecutionInterface
    {
        return $this->setData(self::SUMMARY, $summary);
    }

    /**
     * @inheritDoc
     */
    public function getSummaryInfo(string $key)
    {
        $summary = $this->getSummary();

        return isset($summary[$key]) ? $summary[$key] : '';
    }

    /**
     * @inheritDoc
     */
    public function addSummaryInfo(string $key, $info): StepExecutionInterface
    {
        $summary = $this->getSummary();

        $summary[$key] = $info;

        return $this->setSummary($summary);
    }

    /**
     * @inheritDoc
     */
    public function incrementSummaryInfo(string $key, int $increment = 1): StepExecutionInterface
    {
        $summary = $this->getSummary();

        if (!isset($summary[$key])) {
            $summary[$key] = $increment;
        } else {
            $summary[$key] = $summary[$key] + $increment;
        }

        return $this->setSummary($summary);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave()
    {
        $status = $this->getData(self::STATUS);

        if ($status instanceof BatchStatus) {
            $this->setData(self::STATUS, $status->getValue());
        }

        $failureExceptions = $this->getData(self::FAILURE_EXCEPTIONS);

        if (is_array($failureExceptions)) {
            $this->setData(self::FAILURE_EXCEPTIONS, $this->serializer->serialize($failureExceptions));
        }

        $errors = $this->getData(self::ERRORS);

        if (is_array($errors)) {
            $this->setData(self::ERRORS, $this->serializer->serialize($errors));
        }

        $summary = $this->getData(self::SUMMARY);

        if (is_array($summary)) {
            $this->setData(self::SUMMARY, $this->serializer->serialize($summary));
        }

        return parent::beforeSave();
    }
}