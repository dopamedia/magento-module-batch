<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 16.10.17
 */

namespace Dopamedia\Batch\Item;

use Dopamedia\PhpBatch\Step\StepExecutionAwareInterface;

/**
 * Interface CharsetValidatorInterface
 * @package Dopamedia\Batch\Item
 */
interface CharsetValidatorInterface extends StepExecutionAwareInterface
{
    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function validate(): void;
}