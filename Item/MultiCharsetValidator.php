<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 16.10.17
 */

namespace Dopamedia\Batch\Item;

use Dopamedia\PhpBatch\Step\StepExecutionAwareTrait;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class MultiCharsetValidator
 * @package Dopamedia\Batch\Item
 */
class MultiCharsetValidator implements CharsetValidatorInterface
{
    use StepExecutionAwareTrait;

    /**
     * @var CharsetValidator
     */
    private $charsetValidator;

    /**
     * @var Finder
     */
    private $finder;

    /**
     * MultiCharsetValidator constructor.
     * @param CharsetValidator $charsetValidator
     * @param Finder $finder
     */
    public function __construct(
        CharsetValidator $charsetValidator,
        Finder $finder
    )
    {
        $this->charsetValidator = $charsetValidator;
        $this->finder = $finder;
    }

    /**
     * @inheritdoc
     */
    public function validate(): void
    {
        /** @var SplFileInfo $fileInfo */
        foreach ($this->createFilesIterator() as $fileInfo) {
            $this->initializeCharsetValidator($fileInfo)->validate();
        }
    }

    /**
     * @param SplFileInfo $fileInfo
     * @return CharsetValidator
     */
    private function initializeCharsetValidator(SplFileInfo $fileInfo): CharsetValidator
    {
        $charsetValidator = clone $this->charsetValidator;
        $this->stepExecution->getJobParameters()->set('filePath', $fileInfo->getRealPath());
        $charsetValidator->setStepExecution($this->getStepExecution());

        return $charsetValidator;
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