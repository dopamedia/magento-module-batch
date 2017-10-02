<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 02.10.17
 */

namespace Dopamedia\Batch\Item;

use Dopamedia\PhpBatch\Step\StepExecutionAwareInterface;
use Dopamedia\PhpBatch\Step\StepExecutionAwareTrait;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class CharsetValidator
 * @package Dopamedia\Batch\Item
 */
class CharsetValidator implements StepExecutionAwareInterface
{
    use StepExecutionAwareTrait;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $charset;

    /**
     * @var int
     */
    private $maxErrors;

    /**
     * CharsetValidator constructor.
     * @param Filesystem $filesystem
     * @param string $charset
     * @param int $maxErrors
     */
    public function __construct(
        Filesystem $filesystem,
        string $charset = 'UTF-8',
        int $maxErrors = 10
    )
    {
        $this->filesystem = $filesystem;
        $this->charset = $charset;
        $this->maxErrors = $maxErrors;
    }

    /**
     * @throws FileSystemException
     * @throws LocalizedException
     * @return void
     */
    public function validate(): void
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $filePath = $jobParameters->get('filePath');
        $this->validateEncoding($filePath);
    }

    /**
     * @param string $filePath
     * @throws FileSystemException
     * @throws LocalizedException
     * @return void
     */
    private function validateEncoding(string $filePath): void
    {
        if ($this->filesystem->exists($filePath) === false) {
            throw new FileSystemException(new Phrase('Unable to read the file "%1".', [$filePath]));
        }

        $handle = fopen($filePath, 'r');

        $errors = [];
        $lineNo = 0;

        while ((false !== $line = fgets($handle)) &&
            (count($errors) < $this->maxErrors)
        ) {
            $lineNo++;
            if (false === @iconv($this->charset, $this->charset, $line)) {
                $errors[] = $lineNo;
            }
        }

        fclose($handle);

        if (count($errors) > 0) {
            $message = count($errors) === $this->maxErrors ?
                sprintf('The first %s erroneous lines are %s.', $this->maxErrors, implode(', ', $errors)) :
                sprintf('The lines %s are erroneous.', implode(', ', $errors));

            throw new LocalizedException(new Phrase(
                'The file "%1" is not correctly encoded in %2 %3',
                [$filePath, $this->charset, $message]
            ));
        }

        $this->stepExecution->addSummaryInfo(
            __('Charset Validator'),
            sprintf('%s OK', $this->charset)
        );
    }
}