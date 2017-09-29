<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 28.09.17
 */

namespace Dopamedia\Batch\Model;

use Dopamedia\Batch\Api\StepExecutionRepositoryInterface;
use Dopamedia\PhpBatch\StepExecutionInterface;
use Dopamedia\Batch\Model\ResourceModel\StepExecution as ResourceStepExecution;
use Dopamedia\Batch\Model\StepExecutionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Phrase;

/**
 * Class StepExecutionRepository
 * @package Dopamedia\Batch\Model
 */
class StepExecutionRepository implements StepExecutionRepositoryInterface
{
    /**
     * @var ResourceStepExecution
     */
    private $resource;

    /**
     * @var \Dopamedia\Batch\Model\StepExecutionFactory
     */
    private $stepExecutionFactory;

    /**
     * StepExecutionRepository constructor.
     * @param ResourceStepExecution $resource
     * @param \Dopamedia\Batch\Model\StepExecutionFactory $stepExecutionFactory
     */
    public function __construct(
        ResourceStepExecution $resource,
        StepExecutionFactory $stepExecutionFactory
    )
    {
        $this->resource = $resource;
        $this->stepExecutionFactory = $stepExecutionFactory;
    }

    /**
     * @param int $stepExecutionId
     * @return StepExecutionInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $stepExecutionId): StepExecutionInterface
    {
        /** @var StepExecution $stepExecution */
        $stepExecution = $this->stepExecutionFactory->create();
        $this->resource->load($stepExecution, $stepExecutionId);
        if ($stepExecution->getId() === null) {
            throw new NoSuchEntityException(
                new Phrase('StepExecution with id "%1" does not exist.', [$stepExecutionId])
            );
        }

        return $stepExecution;
    }

    /**
     * @param StepExecutionInterface|AbstractModel $stepExecution
     * @return StepExecutionInterface
     * @throws CouldNotSaveException
     */
    public function save(StepExecutionInterface $stepExecution): StepExecutionInterface
    {
        try {
            $this->resource->save($stepExecution);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }

        return $stepExecution;
    }
}