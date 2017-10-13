<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 02.10.17
 */

namespace Dopamedia\Batch\Writer\Database;

use Dopamedia\Batch\Model\Import\Source\ArraySourceFactory;
use Dopamedia\Batch\Model\Import\Source\ArraySource;
use Dopamedia\Batch\ArrayConverter\FlatToStandard\Products as ProductsArrayConverter;
use Dopamedia\PhpBatch\Item\ItemWriterInterface;
use Magento\ImportExport\Model\Import as ImportModel;
use Magento\ImportExport\Model\ImportFactory as ImportModelFactory;

/**
 * Class ProductWriter
 * @package Dopamedia\Batch\Writer\Database
 */
class ProductWriter extends AbstractWriter implements ItemWriterInterface
{
    /**
     * @var ProductsArrayConverter
     */
    private $productsArrayConverter;

    /**
     * @var ImportModelFactory
     */
    private $importModelFactory;

    /**
     * @var ArraySourceFactory
     */
    private $arraySourceFactory;

    /**
     * @var array
     */
    private $options;

    /**
     * @var ImportModel
     */
    private $importModel;

    /**
     * ProductWriter constructor.
     * @param ProductsArrayConverter $productsArrayConverter
     * @param ImportModelFactory $importModelFactory
     * @param ArraySourceFactory $arraySourceFactory
     * @param array $options
     */
    public function __construct(
        ProductsArrayConverter $productsArrayConverter,
        ImportModelFactory $importModelFactory,
        ArraySourceFactory $arraySourceFactory,
        array $options = []
    )
    {
        $this->productsArrayConverter = $productsArrayConverter;
        $this->importModelFactory = $importModelFactory;
        $this->arraySourceFactory = $arraySourceFactory;
        $this->options = $options;
    }

    /**
     * @param array $items
     */
    public function write(array $items)
    {
        $items = $this->productsArrayConverter->convert($items);

        if ($this->importModel === null) {
            $this->importModel = $this->createImportModel();
        }

        /** @var ArraySource $source */
        $source = $this->arraySourceFactory->create(['data' => $items]);

        $validationResults = $this->importModel->validateSource($source);

        if ($validationResults !== true) {
            $this->stepExecution->addError($this->importModel->getFormatedLogTrace());
        } else {
            try {
                $result = $this->importModel->importSource();

                $this->incrementSummaryInfo();

                if ($result === false) {
                    $this->stepExecution->addError($this->importModel->getFormatedLogTrace());
                }

            } catch (\Exception $e) {
                $this->stepExecution->addError($e->getMessage());
                $this->stepExecution->addError($this->importModel->getFormatedLogTrace());
            }
        }
    }

    /**
     * @return ImportModel
     */
    private function createImportModel(): ImportModel
    {
        $jobParameters = $this->stepExecution->getJobParameters();

        $importOptions = $jobParameters->has('import_options') ? (array)$jobParameters->get('import_options') : [];

        /** @var ImportModel $importModel */
        return $this->importModelFactory->create()->setData(array_merge($this->options, $importOptions));
    }

    /**
     * @return void
     */
    private function incrementSummaryInfo(): void
    {
        $this->stepExecution->addSummaryInfo('update', $this->importModel->getUpdatedItemsCount());
        $this->stepExecution->addSummaryInfo('create', $this->importModel->getCreatedItemsCount());
        $this->stepExecution->addSummaryInfo('delete', $this->importModel->getDeletedItemsCount());
    }
}