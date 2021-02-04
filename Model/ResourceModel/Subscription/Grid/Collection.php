<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model\ResourceModel\Subscription\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{
    protected function _initSelect()
    {
        $this->getSelect()->joinLeft(
            ['orders' => $this->getTable('sales_order')],
            'main_table.original_order_id = orders.entity_id',
            'increment_id'
        );
        return parent::_initSelect();
    }
}
