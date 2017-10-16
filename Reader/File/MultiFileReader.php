<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 16.10.17
 */

namespace Dopamedia\Batch\Reader\File;

use Dopamedia\PhpBatch\Item\InitializableInterface;
use Dopamedia\PhpBatch\Item\InvalidItemException;
use Dopamedia\PhpBatch\Item\ItemReaderInterface;
use Dopamedia\PhpBatch\Step\StepExecutionAwareInterface;
use Dopamedia\PhpBatch\Step\StepExecutionAwareTrait;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class MultiItemReader
 * @package Dopamedia\Batch\Reader\File
 */
class MultiFileReader implements ItemReaderInterface, StepExecutionAwareInterface
{
    use StepExecutionAwareTrait;

    /**
     * @var ItemReaderInterface
     */
    private $fileReader;

    /**
     * @var Finder
     */
    private $finder;

    /**
     * @var null|\Iterator
     */
    private $filesIterator;

    /**
     * @var null|ItemReaderInterface
     */
    private $currentFileReader;

    /**
     * MultiItemReader constructor.
     * @param ItemReaderInterface $fileReader
     * @param Finder $finder
     */
    public function __construct(
        ItemReaderInterface $fileReader,
        Finder $finder
    )
    {
        $this->fileReader = $fileReader;
        $this->finder = $finder;
    }

    /**
     * @inheritDoc
     */
    public function read()
    {
        if ($this->filesIterator === null) {
            $this->filesIterator = $this->createFilesIterator();
            $this->filesIterator->rewind();
        }

        /** @var SplFileInfo $fileInfo */
        $fileInfo = $this->filesIterator->current();

        if ($fileInfo === null) {
            return null;
        }

        return $this->readNextItem($fileInfo);
    }

    /**
     * @param SplFileInfo $fileInfo
     * @return mixed
     * @throws InvalidItemException
     */
    private function readNextItem(SplFileInfo $fileInfo)
    {
        if ($this->currentFileReader === null) {
            $this->currentFileReader = $this->initializeCurrentFileReader($fileInfo);
        }

        $item = $this->currentFileReader->read();

        if ($item === null) {
            $this->filesIterator->next();
            if ($this->filesIterator->valid()) {
                $this->currentFileReader = null;
                $item = $this->readNextItem($this->filesIterator->current());
            }
        }

        return $item;
    }

    /**
     * @param SplFileInfo $fileInfo
     * @return ItemReaderInterface
     */
    private function initializeCurrentFileReader(SplFileInfo $fileInfo): ItemReaderInterface
    {
        if ($this->currentFileReader === null) {
            $this->currentFileReader = clone $this->fileReader;
        }

        if ($this->currentFileReader instanceof StepExecutionAwareInterface) {
            $this->currentFileReader->setStepExecution($this->getStepExecution());
        }

        if ($this->currentFileReader instanceof InitializableInterface) {
            $this->currentFileReader->initialize();
        }

        $this->stepExecution->getJobParameters()->set('filePath', $fileInfo->getRealPath());

        return $this->currentFileReader;
    }

    /**
     * @return \Iterator
     */
    private function createFilesIterator(): \Iterator
    {
        $filePattern = $this->stepExecution->getJobParameters()->get('filePattern');

        $dirName = pathinfo($filePattern, PATHINFO_DIRNAME);
        $baseName = basename($filePattern);

        return $this->finder->in($dirName)->name($baseName)->getIterator();
    }
}