<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 27.09.17
 */

namespace Dopamedia\Batch\Model;

use Dopamedia\Batch\Api\StepExecutionRepositoryInterface;
use Dopamedia\Batch\Model\ResourceModel\Warning as ResourceWarning;
use Dopamedia\PhpBatch\StepExecutionInterface;
use Dopamedia\PhpBatch\WarningInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Serialize\Serializer\Json as Serializer;

/**
 * Class Warning
 * @package Dopamedia\Batch\Model
 */
class Warning extends AbstractModel implements WarningInterface, SerializableFieldsInterface
{
    use SerializableFieldsTrait;

    /**#@+*/
    public const ID = 'id';
    public const STEP_EXECUTION_ID = 'step_execution_id';
    public const REASON = 'reason';
    public const REASON_PARAMETERS = 'reason_parameters';
    public const ITEM = 'item';
    /**#@-*/

    /**
     * @var StepExecutionRepositoryInterface
     */
    private $stepExecutionRepository;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var null|StepExecutionInterface
     */
    private $stepExecution = null;

    /**
     * Warning constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param StepExecutionRepositoryInterface $stepExecutionRepository
     * @param Serializer $serializer
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        StepExecutionRepositoryInterface $stepExecutionRepository,
        Serializer $serializer,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->stepExecutionRepository = $stepExecutionRepository;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ResourceWarning::class);
    }

    /**
     * @inheritDoc
     */
    public function getSerializableFields(): array
    {
        return [self::REASON_PARAMETERS, self::ITEM];
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
     * @return int|null
     */
    public function getStepExecutionId(): ?int
    {
        return $this->getData(self::STEP_EXECUTION_ID);
    }

    /**
     * @param int $stepExecutionId
     * @return WarningInterface
     */
    public function setStepExecutionId(int $stepExecutionId): WarningInterface
    {
        return $this->setData(self::STEP_EXECUTION_ID, $stepExecutionId);
    }

    /**
     * @return StepExecutionInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStepExecution(): ?StepExecutionInterface
    {
        if ($this->getStepExecutionId() === null) {
            return null;
        }

        if ($this->stepExecution === null) {
            $this->stepExecution = $this->stepExecutionRepository->getById($this->getStepExecutionId());
        }

        return $this->stepExecution;
    }

    /**
     * @inheritDoc
     */
    public function setStepExecution(StepExecutionInterface $stepExecution): WarningInterface
    {
        $this->stepExecution = $stepExecution;
        $this->setStepExecutionId($stepExecution->getId());

        return $this;
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function getReason(): ?string
    {
        return $this->getData(self::REASON);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function setReason(string $reason): WarningInterface
    {
        return $this->setData(self::REASON, $reason);
    }

    /**
     * @inheritDoc
     */
    public function getReasonParameters(): array
    {
        return $this->getSerializedData($this->serializer, self::REASON_PARAMETERS);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function setReasonParameters(array $reasonParameters): WarningInterface
    {
        return $this->setData(self::REASON_PARAMETERS, $reasonParameters);
    }

    /**
     * @inheritDoc
     */
    public function getItem(): ?array
    {
        return $this->getSerializedData($this->serializer, self::ITEM);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function setItem(array $item): WarningInterface
    {
        return $this->setData(self::ITEM, $item);
    }

    /**
     * @inheritDoc
     */
    public function beforeSave()
    {
        $this->serializeDataBeforeSave($this->serializer);

        return parent::beforeSave();
    }
}