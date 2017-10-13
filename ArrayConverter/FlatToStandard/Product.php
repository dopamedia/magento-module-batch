<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 09.10.17
 */

namespace Dopamedia\Batch\ArrayConverter\FlatToStandard;

use Dopamedia\Batch\ArrayConverter\ArrayConverterInterface;
use Magento\Store\Model\Store;
use Magento\CatalogImportExport\Model\Import\Product as ProductImportModel;

/**
 * Class Product
 * @package Dopamedia\Batch\ArrayConverter\FlatToStandard
 */
class Product implements ArrayConverterInterface
{
    public const STORE_VIEW_SPECIFIC_MARKER = '--store_view-';

    /**
     * @var string[]
     */
    private static $defaultImportColNames = [
        ProductImportModel::COL_SKU,
        ProductImportModel::COL_ATTR_SET,
        ProductImportModel::COL_TYPE
    ];

    /**
     * @inheritDoc
     */
    public function convert(array $items): array
    {
        $convertedItem = [];

        foreach ($items as $colName => $value) {
            if ($this->isColumnStoreViewSpecific($colName)) {
                $convertedItem[$this->extractStoreViewCode($colName)][$this->extractColumnName($colName)] = $value;
            } else {
                $convertedItem[Store::ADMIN_CODE][$colName] = $value;
            }
        }

        foreach ($convertedItem as $storeViewCode => $storeViewColumn) {
            if ($storeViewCode !== Store::ADMIN_CODE) {
                foreach (self::$defaultImportColNames as $defaultImportColName) {
                    if (array_key_exists($defaultImportColName, $convertedItem[Store::ADMIN_CODE])) {
                        $convertedItem[$storeViewCode][$defaultImportColName] = $convertedItem[Store::ADMIN_CODE][$defaultImportColName];
                    }
                }

                $convertedItem[$storeViewCode][ProductImportModel::COL_STORE_VIEW_CODE] = $storeViewCode;
            }
        }

        return array_values($convertedItem);
    }

    /**
     * @param string $colName
     * @return bool
     */
    private function isColumnStoreViewSpecific(string $colName): bool
    {
        return strpos($colName, self::STORE_VIEW_SPECIFIC_MARKER);
    }

    /**
     * @param string $colName
     * @return string
     */
    private function extractStoreViewCode(string $colName): string
    {
        return substr(
            $colName,
            strpos(
                $colName,
                self::STORE_VIEW_SPECIFIC_MARKER) + strlen(self::STORE_VIEW_SPECIFIC_MARKER
            )
        );
    }

    private function extractColumnName(string $colName): string
    {
        return substr($colName, 0, strpos($colName, self::STORE_VIEW_SPECIFIC_MARKER));
    }
}