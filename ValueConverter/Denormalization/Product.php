<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 13.10.17
 */

namespace Dopamedia\Batch\ValueConverter\Denormalization;

use Dopamedia\Batch\ValueConverter\ValueConverterInterface;
use Magento\ImportExport\Model\Import as ImportModel;

/**
 * Class Product
 * @package Dopamedia\Batch\ValueConverter\Denormalization
 */
class Product implements ValueConverterInterface
{
    /**
     * @inheritDoc
     */
    public function convert(array $item): array
    {
        foreach ($item as $colName => &$value) {
            if (is_array($value)) {
                if (is_array(current($value))) {
                    $this->convertNestedArray($value);
                } else {
                    $this->convertSimpleArray($value);
                }
            }
        }

        return $item;
    }

    /**
     * @param array $value
     */
    private function convertSimpleArray(array &$value): void
    {
        $value = implode(
            ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR,
            $value
        );
    }

    /**
     * @param array $value
     */
    private function convertNestedArray(array &$value): void
    {
        $implodeString = ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR;

        $arr = array_map(
            function ($value, $key) use (&$implodeString) {
                if (is_array($value) && is_numeric($key)) {
                    $this->convertNestedArray($value);
                    $implodeString = '|';

                    return $value;
                }

                return sprintf("%s=%s", $key, $value);
            },
            $value,
            array_keys($value)
        );

        $value = implode(
            $implodeString, $arr
        );
    }
}