<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 08.10.17
 */

namespace Dopamedia\Batch\Ui\Component\Listing\Columns;

use Dopamedia\PhpBatch\BatchStatus;
use Magento\Ui\Component\Listing\Columns;

/**
 * Class BatchStatusColumns
 * @package Dopamedia\Batch\Ui\Component\Listing\Columns
 */
class BatchStatusColumns extends Columns
{
    /**
     * @param int $valueId
     * @return string
     */
    private function getLabelById(int $valueId): string
    {
        $allLabels = BatchStatus::getAllLabels();

        if (array_key_exists($valueId, $allLabels)) {
            return __($allLabels[$valueId]);
        }

        return __($allLabels[BatchStatus::UNKNOWN]);
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['status'])) {
                    $item['status'] = $this->getLabelById($item['status']);
                }
            }
        }

        return $dataSource;
    }
}