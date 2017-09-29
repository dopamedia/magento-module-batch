<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 27.09.17
 */

namespace Dopamedia\Batch\Api;

use Dopamedia\PhpBatch\StepExecutionInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface StepExecutionRepositoryInterface
 * @package Dopamedia\Batch\Api
 */
interface StepExecutionRepositoryInterface
{
    /**
     * @param int $stepExecutionId
     * @return StepExecutionInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $stepExecutionId): StepExecutionInterface;

    /**
     * @param StepExecutionInterface $stepExecution
     * @return StepExecutionInterface
     * @throws CouldNotSaveException
     */
    public function save(StepExecutionInterface $stepExecution): StepExecutionInterface;

}