<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 08.10.17
 */

namespace Dopamedia\Batch\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Escaper;

/**
 * Class InstanceActions
 * @package Dopamedia\Batch\Ui\Component\Listing\Column
 */
class InstanceActions extends Column
{
    const URL_PATH_INFO = 'batch/jobInstance/info';
    const URL_PATH_DELETE = 'batch/jobInstance/delete';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * InstanceActions constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param Escaper $escaper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $code = $this->escaper->escapeHtml($item['code']);
                $item[$this->getData('name')] = [
                    'info' => [
                        'href' => $this->urlBuilder->getUrl(
                            self::URL_PATH_INFO,
                            [
                                'id' => $item['id']
                            ]
                        ),
                        'label' => __('Info')
                    ],
                    'delete' => [
                        'href' => $this->urlBuilder->getUrl(
                            self::URL_PATH_DELETE,
                            [
                                'id' => $item['id']
                            ]
                        ),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete %1', $code),
                            'message' => __('Are you sure you want to delete a %1 record?', $code)
                        ]
                    ]
                ];
            }
        }

        return $dataSource;
    }
}