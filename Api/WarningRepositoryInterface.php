<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 28.09.17
 */

namespace Dopamedia\Batch\Api;

use Dopamedia\PhpBatch\WarningInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface WarningRepositoryInterface
 * @package Dopamedia\Batch\Api
 */
interface WarningRepositoryInterface
{
    /**
     * @param int $warningId
     * @return WarningInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $warningId): WarningInterface;

    /**
     * @param WarningInterface $warning
     * @return WarningInterface
     * @throws CouldNotSaveException
     */
    public function save(WarningInterface $warning): WarningInterface;

}