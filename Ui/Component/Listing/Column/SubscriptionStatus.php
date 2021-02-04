<?php

declare(strict_types=1);

namespace PayPal\Subscription\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class SubscriptionStatus extends Column
{
    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                switch ($item['status']) {
                    case 1:
                        $item['status'] = 'Active';
                        break;
                    case 2:
                        $item['status'] = 'Paused';
                        break;
                    case 3:
                        $item['status'] = 'Cancelled';
                        break;
                    case 4:
                        $item['status'] = 'Expired';
                        break;
                }
            }
        }

        return $dataSource;
    }
}
