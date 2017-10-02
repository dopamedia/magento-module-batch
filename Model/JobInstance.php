<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 27.09.17
 */

namespace Dopamedia\Batch\Model;

use Dopamedia\Batch\Model\ResourceModel\JobExecution\Collection as JobExecutionCollection;
use Dopamedia\Batch\Model\ResourceModel\JobExecution\CollectionFactory as JobExecutionCollectionFactory;
use Dopamedia\Batch\Model\ResourceModel\JobInstance as ResourceJobInstance;
use Dopamedia\PhpBatch\JobExecutionInterface;
use Dopamedia\PhpBatch\JobInstanceInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Magento\Framework\Serialize\SerializerInterface;

class JobInstance extends AbstractModel implements JobInstanceInterface, SerializableFieldsInterface
{

    use SerializableFieldsTrait;

    /**#@+*/
    public const ID = 'id';
    public const CODE = 'code';
    public const LABEL = 'label';
    public const JOB_NAME = 'job_name';
    public const STATUS = 'status';
    public const CONNECTOR = 'connector';
    public const TYPE = 'type';
    public const RAW_PARAMETERS = 'raw_parameters';
    /**#@-*/

    /**
     * @var JobExecutionCollectionFactory
     */
    private $jobExecutionCollectionFactory;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var null|JobExecutionCollection
     */
    private $jobExecutionCollection;

    /**
     * JobInstance constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param JobExecutionCollectionFactory $jobExecutionCollectionFactory
     * @param Serializer $serializer
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        JobExecutionCollectionFactory $jobExecutionCollectionFactory,
        Serializer $serializer,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->jobExecutionCollectionFactory = $jobExecutionCollectionFactory;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ResourceJobInstance::class);
    }

    /**
     * @inheritDoc
     */
    public function getSerializableFields(): array
    {
        return [self::RAW_PARAMETERS];
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
    public function getCode(): ?string
    {
        return $this->getData(self::CODE);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function setCode(string $code): JobInstanceInterface
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function getJobName(): ?string
    {
        return $this->getData(self::JOB_NAME);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function setJobName(string $jobName): JobInstanceInterface
    {
        return $this->setData(self::JOB_NAME, $jobName);
    }

    /**
     * @inheritDoc
     */
    public function getRawParameters(): array
    {
        return $this->getSerializedData( $this->serializer, self::RAW_PARAMETERS);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function setRawParameters(array $rawParameters): JobInstanceInterface
    {
        return $this->setData(self::RAW_PARAMETERS, $rawParameters);
    }

    /**
     * @return JobExecutionCollection
     */
    private function getJobExecutionCollection(): JobExecutionCollection
    {
        if ($this->jobExecutionCollection === null) {
            $this->jobExecutionCollection = $this->jobExecutionCollectionFactory
                ->create()
                ->setJobInstanceFilter($this);
        }

        return $this->jobExecutionCollection;
    }

    /**
     * @inheritDoc
     */
    public function getJobExecutions(): array
    {
        return $this->getJobExecutionCollection()->getItems();
    }

    /**
     * @param JobExecutionInterface|DataObject $jobExecution
     * @return JobInstanceInterface
     * @throws \Exception
     */
    public function addJobExecution(JobExecutionInterface $jobExecution): JobInstanceInterface
    {
        $this->getJobExecutionCollection()->addItem($jobExecution);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeJobExecution(JobExecutionInterface $jobExecution): JobInstanceInterface
    {
        $this->getJobExecutionCollection()->removeItemByKey($jobExecution->getId());

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave()
    {
        $this->serializeDataBeforeSave($this->serializer);

        return parent::beforeSave();
    }
}