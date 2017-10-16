<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 02.10.17
 */

namespace Dopamedia\Batch\Step;

use Dopamedia\Batch\Item\CharsetValidatorInterface;
use Dopamedia\PhpBatch\Adapter\EventManagerAdapterInterface;
use Dopamedia\PhpBatch\Repository\JobRepositoryInterface;
use Dopamedia\PhpBatch\Step\AbstractStep;
use Dopamedia\PhpBatch\StepExecutionInterface;

/**
 * Class ValidatorStep
 * @package Dopamedia\Batch\Step
 */
class ValidatorStep extends AbstractStep
{
    /**
     * @var CharsetValidatorInterface
     */
    private $charsetValidator;

    /**
     * @inheritDoc
     */
    public function __construct(
        string $name,
        EventManagerAdapterInterface $eventManagerAdapter,
        JobRepositoryInterface $jobRepository,
        CharsetValidatorInterface $charsetValidator
    )
    {
        parent::__construct($name, $eventManagerAdapter, $jobRepository);

        $this->charsetValidator = $charsetValidator;
    }

    /**
     * @param StepExecutionInterface $stepExecution
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function doExecute(StepExecutionInterface $stepExecution): void
    {
        $this->charsetValidator->setStepExecution($stepExecution);
        $this->charsetValidator->validate();
    }
}