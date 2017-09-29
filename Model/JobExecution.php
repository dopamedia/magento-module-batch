<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 27.09.17
 */

namespace Dopamedia\Batch\Model;

use Dopamedia\Batch\Api\JobInstanceRepositoryInterface;
use Dopamedia\Batch\Model\ResourceModel\JobExecution as ResourceJobExecution;
use Dopamedia\Batch\Model\ResourceModel\StepExecution\Collection as StepExecutionCollection;
use Dopamedia\Batch\Model\ResourceModel\StepExecution\CollectionFactory as StepExecutionCollectionFactory;
use Dopamedia\PhpBatch\BatchStatus;
use Dopamedia\PhpBatch\ExitStatus;
use Dopamedia\PhpBatch\Item\ExecutionContext;
use Dopamedia\PhpBatch\Job\RuntimeErrorException;
use Dopamedia\PhpBatch\JobExecutionInterface;
use Dopamedia\PhpBatch\JobInstanceInterface;
use Dopamedia\PhpBatch\Job\JobParameters;
use Dopamedia\PhpBatch\StepExecutionInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Serialize\Serializer\Json as Serializer;

class JobExecution extends AbstractModel implements JobExecutionInterface
{
    /**#@+*/
    public const ID = 'id';
    public const PID = 'pid';
    public const STATUS = 'status';
    public const START_TIME = 'start_time';
    public const END_TIME = 'end_time';
    public const CREATE_TIME = 'create_time';
    public const EXIT_CODE = 'exit_code';
    public const EXIT_DESCRIPTION = 'exit_description';
    public const FAILURE_EXCEPTIONS = 'failure_exceptions';
    public const LOG_FILE = 'log_file';
    public const JOB_INSTANCE_ID = 'job_instance_id';
    /**#@-*/

    /**
     * @var JobInstanceRepositoryInterface
     */
    private $jobInstanceRepository;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var StepExecutionFactory
     */
    private $stepExecutionFactory;

    /**
     * @var StepExecutionCollectionFactory
     */
    private $stepExecutionCollectionFactory;

    /**
     * @var null|JobInstanceInterface
     */
    private $jobInstance = null;

    /**
     * @var null|ExitStatus
     */
    private $exitStatus = null;

    /**
     * @var null|ExecutionContext
     */
    private $executionContext = null;

    /**
     * @var null|JobParameters
     */
    private $jobParameters = null;

    /**
     * @var null|StepExecutionCollection
     */
    private $stepExecutionCollection = null;

    /**
     * JobExecution constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param JobInstanceRepositoryInterface $jobInstanceRepository
     * @param Serializer $serializer
     * @param StepExecutionFactory $stepExecutionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        JobInstanceRepositoryInterface $jobInstanceRepository,
        Serializer $serializer,
        StepExecutionFactory $stepExecutionFactory,
        StepExecutionCollectionFactory $stepExecutionCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ){
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->serializer = $serializer;
        $this->stepExecutionFactory = $stepExecutionFactory;
        $this->stepExecutionCollectionFactory = $stepExecutionCollectionFactory;
        $this->setCreateTime(new \DateTime());
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ResourceJobExecution::class);
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
        return $this->setData(self::ID, $id);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function getJobParameters(): ?JobParameters
    {
        return $this->jobParameters;
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function setJobParameters(JobParameters $jobParameters): JobExecutionInterface
    {
        $this->jobParameters = $jobParameters;

        return $this;
    }

    /**
     * @return JobInstanceInterface|null
     * @throws NoSuchEntityException
     */
    public function getJobInstance(): ?JobInstanceInterface
    {
        if ($this->getJobInstanceId() === null) {
            return null;
        }

        if ($this->jobInstance === null) {
            $this->jobInstance = $this->jobInstanceRepository->getById($this->getJobInstanceId());
        }

        return $this->jobInstance;
    }

    /**
     * @inheritDoc
     */
    public function setJobInstance(JobInstanceInterface $jobInstance): JobExecutionInterface
    {
        $this->jobInstance = $jobInstance;
        $this->setJobInstanceId($jobInstance->getId());

        return $this;
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function getPid(): ?int
    {
        return $this->getData(self::PID);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function setPid(int $pid): JobExecutionInterface
    {
        return $this->setData(self::PID, $pid);
    }

    /**
     * @return int|null
     */
    public function getJobInstanceId(): ?int
    {
        return $this->getData(self::JOB_INSTANCE_ID);
    }

    /**
     * @param int $jobInstanceId
     * @return JobExecutionInterface
     */
    public function setJobInstanceId(int $jobInstanceId): JobExecutionInterface
    {
        return $this->setData(self::JOB_INSTANCE_ID, $jobInstanceId);
    }

    /**
     * @inheritDoc
     */
    public function createStepExecution(string $name): StepExecutionInterface
    {
        return $this->stepExecutionFactory->create()
            ->setStepName($name)
            ->setJobExecution($this);
    }

    /**
     * @param StepExecutionInterface|DataObject $stepExecution
     * @return JobExecutionInterface
     * @throws \Exception
     */
    public function addStepExecution(StepExecutionInterface $stepExecution): JobExecutionInterface
    {
        $this->getStepExecutionCollection()->addItem($stepExecution);

        return $this;
    }

    /**
     * @return StepExecutionCollection
     */
    private function getStepExecutionCollection(): StepExecutionCollection
    {
        if ($this->stepExecutionCollection === null) {
            $this->stepExecutionCollection = $this->stepExecutionCollectionFactory
                ->create()
                ->setJobExecutionFilter($this);
        }

        return $this->stepExecutionCollection;
    }

    /**
     * @inheritDoc
     */
    public function upgradeStatus(BatchStatus $status): JobExecutionInterface
    {
        $newBatchStatus = $this->getStatus();
        $newBatchStatus->upgradeTo($status);
        $this->setStatus($newBatchStatus);

        return $this;
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
     */
    public function setStatus(BatchStatus $status): JobExecutionInterface
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function getStartTime(): \DateTime
    {
        return $this->getData(self::START_TIME);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function setStartTime(\DateTime $startTime): JobExecutionInterface
    {
        return $this->setData(self::START_TIME, $startTime);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function getCreateTime(): \DateTime
    {
        return $this->getData(self::CREATE_TIME);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function setCreateTime(\DateTime $createTime): JobExecutionInterface
    {
        return $this->setData(self::CREATE_TIME, $createTime);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function getEndTime(): \DateTime
    {
        return $this->getData(self::END_TIME);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function setEndTime(\DateTime $endTime): JobExecutionInterface
    {
        return $this->setData(self::END_TIME, $endTime);
    }

    /**
     * @inheritDoc
     */
    public function getExitStatus(): ExitStatus
    {
        if ($this->exitStatus === null) {
            $this->setExitStatus(new ExitStatus(ExitStatus::UNKNOWN));
        }

        return $this->exitStatus;
    }

    /**
     * @inheritDoc
     */
    public function setExitStatus(ExitStatus $exitStatus): JobExecutionInterface
    {
        $this->exitStatus = $exitStatus;
        $this->setData(self::EXIT_CODE, $exitStatus->getExitCode());
        $this->setData(self::EXIT_DESCRIPTION, $exitStatus->getExitDescription());

        return $this;
    }

    /**
     * @return null|string
     */
    public function getExitCode(): ?string
    {
        return $this->getData(self::EXIT_CODE);
    }

    /**
     * @return null|string
     */
    public function getExitDescription(): ?string
    {
        return $this->getData(self::EXIT_DESCRIPTION);
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
    public function setExecutionContext(ExecutionContext $executionContext): JobExecutionInterface
    {
        $this->executionContext = $executionContext;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addFailureException(\Throwable $e): JobExecutionInterface
    {
        $failureExceptions = $this->getFailureExceptions();

        $failureExceptions[] = [
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'messageParameters' => $e instanceof RuntimeErrorException ? $e->getMessageParameters() : [],
            'code' => $e->getCode(),
            'trace' => $e->getTraceAsString()
        ];

        return $this->setData(self::FAILURE_EXCEPTIONS, $failureExceptions);
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
    public function getAllFailureExceptions(): array
    {
        $allExceptions = $this->getFailureExceptions();

        /** @var StepExecution $stepExecution */
        foreach ($this->getStepExecutions() as $stepExecution) {
            $allExceptions = array_merge($allExceptions, $stepExecution->getFailureExceptions());
        }

        return $allExceptions;
    }

    /**
     * @inheritDoc
     */
    public function getStepExecutions(): array
    {
        return $this->getStepExecutionCollection()->getItems();
    }

    /**
     * @inheritDoc
     */
    public function isStopping(): bool
    {
        return $this->getStatus()->getValue() === BatchStatus::STOPPING;
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

        return parent::beforeSave();
    }
}