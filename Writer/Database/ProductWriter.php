<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 02.10.17
 */

namespace Dopamedia\Batch\Writer\Database;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Catalog\Model\Product;
use Magento\CatalogImportExport\Model\Import\Product\SkuProcessor;
use Magento\Framework\App\State;
use FireGento\FastSimpleImport\Model\Importer;
use FireGento\FastSimpleImport\Model\ImporterFactory;
use FireGento\FastSimpleImport\Model\Adapters\NestedArrayAdapterFactory;
use Dopamedia\Batch\ArrayConverter\FlatToStandard\Product as ProductArrayConverter;
use Dopamedia\PhpBatch\Item\ItemWriterInterface;
use Magento\ImportExport\Model\Import;

/**
 * Class ProductWriter
 * @package Dopamedia\Batch\Writer\Database
 */
class ProductWriter extends AbstractWriter implements ItemWriterInterface
{
    /**
     * @var SkuProcessor
     */
    private $skuProcessor;

    /**
     * @var State
     */
    private $state;

    /**
     * @var ImporterFactory
     */
    private $importerFactory;

    /**
     * @var NestedArrayAdapterFactory
     */
    private $nestedArrayAdapterFactory;

    /**
     * @var ProductArrayConverter
     */
    private $productArrayConverter;

    /**
     * ProductWriter constructor.
     * @param SkuProcessor $skuProcessor
     * @param State $state
     * @param ImporterFactory $importerFactory
     * @param NestedArrayAdapterFactory $nestedArrayAdapterFactory
     * @param ProductArrayConverter $productArrayConverter
     */
    public function __construct(
        SkuProcessor $skuProcessor,
        State $state,
        ImporterFactory $importerFactory,
        NestedArrayAdapterFactory $nestedArrayAdapterFactory,
        ProductArrayConverter $productArrayConverter
    )
    {
        $this->skuProcessor = $skuProcessor;
        $this->state = $state;
        $this->importerFactory = $importerFactory;
        $this->nestedArrayAdapterFactory = $nestedArrayAdapterFactory;
        $this->productArrayConverter = $productArrayConverter;
    }

    /**
     * @param array $items
     */
    public function write(array $items)
    {
        $convertedItems = [];

        foreach ($items as $item) {
            $convertedItems = array_merge(
                $this->productArrayConverter->convert($item),
                $convertedItems
            );
        }

        foreach ($convertedItems as $convertedItem) {
            $this->incrementCount($convertedItem);
        }

        try {
            $this->state->setAreaCode(FrontNameResolver::AREA_CODE);
        } catch (\Exception $e) {
            // noop
        }

        $importer = $this->createImporter();

        $importer->processImport($convertedItems);
    }

    /**
     * @return Importer
     */
    private function createImporter(): Importer
    {
        /** @var Importer $importer */
        $importer = $this->importerFactory->create();
        $importer->setImportAdapterFactory($this->nestedArrayAdapterFactory);
        $importer->setBehavior(Import::BEHAVIOR_APPEND);
        $importer->setEntityCode(Product::ENTITY);

        return $importer;
    }

    /**
     * @param array $item
     */
    protected function incrementCount(array $item): void
    {
        $oldSkus = $this->skuProcessor->getOldSkus();

        if (in_array(strtolower($item['sku']), array_keys($oldSkus))) {
            $this->stepExecution->incrementSummaryInfo('update');
        } else {
            $this->stepExecution->incrementSummaryInfo('create');
        }
    }
}