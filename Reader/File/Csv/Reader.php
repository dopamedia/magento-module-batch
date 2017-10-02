<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 01.10.17
 */

namespace Dopamedia\Batch\Reader\File\Csv;

use Dopamedia\Batch\Reader\File\FileIteratorInterface;
use Dopamedia\Batch\Reader\File\FileIteratorInterfaceFactory;
use Dopamedia\Batch\Reader\File\HeaderProviderInterface;
use Dopamedia\PhpBatch\Item\FileInvalidItem;
use Dopamedia\PhpBatch\Item\FlushableInterface;
use Dopamedia\PhpBatch\Item\InvalidItemException;
use Dopamedia\PhpBatch\Item\ItemReaderInterface;
use Dopamedia\PhpBatch\Job\JobParameters;
use Dopamedia\PhpBatch\Step\StepExecutionAwareInterface;
use Dopamedia\PhpBatch\Step\StepExecutionAwareTrait;

/**
 * Class Reader
 * @package Dopamedia\Batch\Reader\File\Csv
 */
class Reader implements ItemReaderInterface, StepExecutionAwareInterface, FlushableInterface
{
    use StepExecutionAwareTrait;

    /**
     * @var FileIteratorInterfaceFactory
     */
    private $fileIteratorFactory;

    /**
     * @var array
     */
    private $options;

    /**
     * @var FileIteratorInterface
     */
    private $fileIterator;
    /**
     * @var HeaderProviderInterface
     */
    private $headerProvider;

    /**
     * Reader constructor.
     * @param FileIteratorInterfaceFactory $fileIteratorFactory
     * @param HeaderProviderInterface $headerProvider
     * @param array $options
     */
    public function __construct(
        FileIteratorInterfaceFactory $fileIteratorFactory,
        HeaderProviderInterface $headerProvider,
        array $options = []
    )
    {
        $this->fileIteratorFactory = $fileIteratorFactory;
        $this->headerProvider = $headerProvider;
        $this->options = $options;
    }

    /**
     * @return array|null
     * @throws InvalidItemException
     */
    public function read()
    {
        $jobParameters = $this->stepExecution->getJobParameters();

        if ($this->fileIterator === null) {
            $this->fileIterator = $this->createFileIterator($jobParameters);
            $this->fileIterator->rewind();
        }

        if ($this->headerProvider->doProcessFirstRow() !== true) {
            $this->fileIterator->next();
        }

        if ($this->fileIterator->valid() === true) {
            $this->stepExecution->incrementSummaryInfo('item_position');
        }

        $data = $this->fileIterator->current();

        if ($data === null) {
            return null;
        }

        if ($this->headerProvider->doProcessFirstRow() === true) {
            $this->fileIterator->next();
        }

        return $this->prepareData($data, $jobParameters);
    }

    /**
     * @param array $data
     * @param JobParameters $jobParameters
     * @return array
     * @throws InvalidItemException
     */
    private function prepareData(array $data, JobParameters $jobParameters): array
    {
        $headers = $this->fileIterator->getHeaders();

        $countHeaders = count($headers);
        $countData = count($data);

        $this->checkColumnNumber($countHeaders, $countData, $data, $jobParameters->get('filePath'));

        if ($countHeaders > $countData) {
            $missingValuesCount = $countHeaders - $countData;
            $missingValues = array_fill(0, $missingValuesCount, '');
            $data = array_merge($data, $missingValues);
        }

        return array_combine($headers, $data);
    }

    /**
     * @param JobParameters $jobParameters
     * @return FileIteratorInterface
     */
    private function createFileIterator(JobParameters $jobParameters): FileIteratorInterface
    {
        $defaultOptions = [
            'reader_options' => [
                'fieldDelimiter' => $jobParameters->get('delimiter'),
                'fieldEnclosure' => $jobParameters->get('enclosure'),
            ],
        ];

        return $this->fileIteratorFactory->create([
            'type' => 'csv',
            'filePath' => $jobParameters->get('filePath'),
            'headerProvider' => $this->headerProvider,
            'options' => array_merge($defaultOptions, $this->options)
        ]);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function flush(): void
    {
        $this->fileIterator = null;
    }

    /**
     * @param int $countHeaders
     * @param int $countData
     * @param array $data
     * @param string $filePath
     * @throws InvalidItemException
     */
    protected function checkColumnNumber(int $countHeaders, int $countData, array $data, string $filePath): void
    {
        if ($countHeaders < $countData) {
            throw new InvalidItemException(
                'Expecting to have %1 columns, actually have %2 in %3:%4',
                new FileInvalidItem($data, ($this->stepExecution->getSummaryInfo('item_position'))),
                [
                    $countHeaders,
                    $countData,
                    $filePath,
                    $this->fileIterator->key()
                ]
            );
        }
    }
}