<?php
declare(strict_types=1);

namespace PayPal\Subscription\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class OrderActions extends Column
{
    private const EDIT = 'paypal_subscription/subscriptions/edit';

    /** @var Data */
    private $backendHelper;

    /**
     * OrderActions constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Prepare data source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item['order_id'])) {
                    $url = $this->urlBuilder->getUrl(
                        'sales/order/view/',
                        ['order_id' => $item['order_id']]
                    );
                    $item['order_id'] = '<a href="'.$url.'">'.$item['order_id'].'</a>';
                }
            }
        }
        return $dataSource;
    }
}
