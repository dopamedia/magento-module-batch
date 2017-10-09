<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 09.10.17
 */

namespace Dopamedia\Batch\ArrayConverter\FlatToStandard;

use Dopamedia\Batch\ArrayConverter\ArrayConverterInterface;
use Magento\CatalogImportExport\Model\Import\Product as ProductImportModel;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Directory\Helper\Data;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Product
 * @package Dopamedia\Batch\ArrayConverter\FlatToStandard
 */
class Product implements ArrayConverterInterface
{
    /**
     * @var array
     */
    private $requiredColumnNames = [
        ProductImportModel::COL_SKU,
        ProductImportModel::COL_TYPE,
        ProductImportModel::COL_ATTR_SET
    ];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var null|array
     */
    private $storeViewLocales;

    /**
     * Product constructor.
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     */
    private function getStoreViewLocales(): array
    {
        if ($this->storeViewLocales === null) {
            $storeViewLocales = [];
            /** @var \Magento\Store\Api\Data\StoreInterface $store */
            foreach ($this->storeManager->getStores() as $store) {
                $storeViewLocales[$store->getCode()] = $this->scopeConfig->getValue(
                    Data::XML_PATH_DEFAULT_LOCALE,
                    ScopeInterface::SCOPE_STORE,
                    $store->getCode()
                );
            }

            $this->storeViewLocales = $storeViewLocales;
        }

        return $this->storeViewLocales;
    }

    /**
     * @param string $columnName
     * @return bool
     */
    private function isColumnLocalized(string $columnName): bool
    {
        return in_array($this->getLocaleFromColumnName($columnName), $this->getStoreViewLocales());
    }

    /**
     * @param string $columnName
     * @return string
     */
    private function getLocaleFromColumnName(string $columnName): string
    {
        return substr($columnName, -5);
    }

    /**
     * @param string $columnName
     * @return string
     */
    private function removeLocaleFromColumnName(string $columnName): string
    {
        return substr($columnName, 0, -6);
    }

    /**
     * @param array $localizedColumns
     * @param array $requiredColumns
     * @return array
     */
    private function generateLocalizedItems(array $localizedColumns, array $requiredColumns): array
    {
        $itemsByLocale = [];
        $itemsByStoreView = [];

        if (!empty($localizedColumns)) {
            foreach ($localizedColumns as $localizedColumnName => $value) {
                $locale = $this->getLocaleFromColumnName($localizedColumnName);
                $columnName = $this->removeLocaleFromColumnName($localizedColumnName);
                $itemsByLocale[$locale][$columnName] = $value;
            }

            foreach ($this->getStoreViewLocales() as $storeViewCode => $locale) {
                if (isset($itemsByLocale[$locale])) {
                    $itemsByStoreView[] = array_merge(
                        $itemsByLocale[$locale],
                        $requiredColumns,
                        [ProductImportModel::COL_STORE_VIEW_CODE => $storeViewCode]
                    );
                }
            }
        }

        return $itemsByStoreView;
    }

    /**
     * @inheritDoc
     */
    public function convert(array $item): array
    {
        $localizedColumns = [];
        $requiredColumns = [];

        foreach ($item as $columnName => $value) {
            if (in_array($columnName, $this->requiredColumnNames)) {
                $requiredColumns[$columnName] = $value;
            }

            if ($this->isColumnLocalized($columnName)) {
                $localizedColumns[$columnName] = $value;
                unset($item[$columnName]);
            }
        }

        return array_merge(
            [$item],
            $this->generateLocalizedItems($localizedColumns, $requiredColumns)
        );
    }
}