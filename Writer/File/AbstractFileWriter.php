<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 16.10.17
 */

namespace Dopamedia\Batch\Writer\File;

use Dopamedia\PhpBatch\Item\ItemWriterInterface;
use Dopamedia\PhpBatch\Step\StepExecutionAwareInterface;
use Dopamedia\PhpBatch\Step\StepExecutionAwareTrait;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class AbstractFileWriter
 * @package Dopamedia\Batch\Writer\File
 */
abstract class AbstractFileWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    use StepExecutionAwareTrait;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $datetimeFormat = 'Y-m-d_H-i-s';

    /**
     * AbstractFileWriter constructor.
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param array $placeholders
     * @return string
     */
    public function getPath(array $placeholders = []): string
    {

        $parameters = $this->stepExecution->getJobParameters();
        $filePath = $parameters->get('filePath');

        if (strpos($filePath, '%') !== false) {

            foreach ($this->stepExecution->getJobParameters() as $key => $value) {
                $placeholders[sprintf('%%%s%%', $key)] = $this->sanitize($value);
            }

            $defaultPlaceholders = ['%datetime%' => date($this->datetimeFormat), '%job_code%' => ''];
            $jobExecution = $this->stepExecution->getJobExecution();

            if (null !== $jobExecution->getJobInstance()) {
                $defaultPlaceholders['%job_code%'] = $this->sanitize($jobExecution->getJobInstance()->getCode());
            }

            $replacePairs = array_merge($defaultPlaceholders, $placeholders);
            $filePath = strtr($filePath, $replacePairs);
        }

        return $filePath;
    }

    /**
     * @param string $value
     * @return string
     */
    protected function sanitize(string $value): string
    {
        return preg_replace('#[^A-Za-z0-9\.]#', '_', $value);
    }
}