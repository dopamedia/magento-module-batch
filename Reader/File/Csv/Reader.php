<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 01.10.17
 */

namespace Dopamedia\Batch\Reader\File\Csv;

use Dopamedia\Batch\Reader\File\FileIteratorInterface;
use Dopamedia\Batch\Reader\File\FlatFileIteratorFactory;
use Dopamedia\PhpBatch\Item\FileInvalidItem;
use Dopamedia\PhpBatch\Item\FlushableInterface;
use Dopamedia\PhpBatch\Item\InvalidItemException;
use Dopamedia\PhpBatch\Item\ItemReaderInterface;
use Dopamedia\PhpBatch\Step\StepExecutionAwareInterface;
use Dopamedia\PhpBatch\Step\StepExecutionAwareTrait;

/**
 * Class Reader
 * @package Dopamedia\Batch\Reader\File\Csv
 */
class Reader implements ItemReaderInterface, StepExecutionAwareInterface, FlushableInterface
{
    use StepExecutionAwareTrait;

    private const PARAMETER_KEY_FILE_PATH = 'filePath';
    private const PARAMETER_KEY_DELIMITER = 'delimiter';
    private const PARAMETER_KEY_ENCLOSURE = 'enclosure';

    /**
     * @var FlatFileIteratorFactory
     */
    private $flatFileIteratorFactory;

    /**
     * @var array
     */
    private $options;

    /**
     * @var FileIteratorInterface
     */
    private $flatFileIterator;

    /**
     * Reader constructor.
     * @param FlatFileIteratorFactory $flatFileIteratorFactory
     * @param array $options
     */
    public function __construct(
        FlatFileIteratorFactory $flatFileIteratorFactory,
        array $options = []
    )
    {
        $this->flatFileIteratorFactory = $flatFileIteratorFactory;
        $this->options = $options;
    }

    /**
     * @return array|null
     * @throws InvalidItemException
     */
    public function read()
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $filePath = $jobParameters->get(self::PARAMETER_KEY_FILE_PATH);

        if ($this->flatFileIterator === null) {
            $delimiter = $jobParameters->get(self::PARAMETER_KEY_DELIMITER);
            $enclosure = $jobParameters->get(self::PARAMETER_KEY_ENCLOSURE);
            $defaultOptions = [
                'reader_options' => [
                    'fieldDelimiter' => $delimiter,
                    'fieldEnclosure' => $enclosure,
                ],
            ];

            /** @var FileIteratorInterface flatFileIterator */
            $this->flatFileIterator = $this->flatFileIteratorFactory->create([
                'type' => 'csv',
                'filePath' => $filePath,
                'options' => array_merge($defaultOptions, $this->options)
            ]);

            $this->flatFileIterator->rewind();
        }

        $this->flatFileIterator->next();

        if ($this->flatFileIterator->valid() === true) {
            $this->stepExecution->incrementSummaryInfo('item_position');
        }

        $data = $this->flatFileIterator->current();

        if ($data === null) {
            return null;
        }

        $headers = $this->flatFileIterator->getHeaders();

        $countHeaders = count($headers);
        $countData = count($data);

        $this->checkColumnNumber($countHeaders, $countData, $data, $filePath);

        if ($countHeaders > $countData) {
            $missingValuesCount = $countHeaders - $countData;
            $missingValues = array_fill(0, $missingValuesCount, '');
            $data = array_merge($data, $missingValues);
        }

        return array_combine($headers, $data);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function flush(): void
    {
        $this->flatFileIterator = null;
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
                    $this->flatFileIterator->key()
                ]
            );
        }
    }
}