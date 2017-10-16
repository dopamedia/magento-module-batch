<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 16.10.17
 */

namespace Dopamedia\Batch\Writer\File;

use Dopamedia\PhpBatch\Item\FlushableInterface;
use Dopamedia\PhpBatch\Item\InitializableInterface;
use Dopamedia\PhpBatch\Item\ItemWriterInterface;
use Dopamedia\PhpBatch\Step\StepExecutionAwareInterface;
use Dopamedia\PhpBatch\Step\StepExecutionAwareTrait;

/**
 * Class MultiFileWriter
 * @package Dopamedia\Batch\Writer\File
 */
class MultiFileWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    use StepExecutionAwareTrait;

    /**
     * @var ItemWriterInterface
     */
    private $fileWriter;

    /**
     * MultiFileWriter constructor.
     * @param ItemWriterInterface $fileWriter
     */
    public function __construct(ItemWriterInterface $fileWriter)
    {
        $this->fileWriter = $fileWriter;
    }

    /**
     * @inheritDoc
     */
    public function write(array $items)
    {
        foreach ($items as $item) {
            $this->initializeFileWriter()->write($item);
        }
    }

    /**
     * @return ItemWriterInterface
     */
    private function initializeFileWriter(): ItemWriterInterface
    {
        $fileWriter = clone $this->fileWriter;

        if ($fileWriter instanceof FlushableInterface) {
            $fileWriter->flush();
        }

        if ($fileWriter instanceof InitializableInterface) {
            $fileWriter->initialize();
        }

        if ($fileWriter instanceof StepExecutionAwareInterface) {
            $fileWriter->setStepExecution($this->getStepExecution());
        }

        return $fileWriter;
    }
}