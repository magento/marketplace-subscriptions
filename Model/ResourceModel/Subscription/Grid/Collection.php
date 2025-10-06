<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model\ResourceModel\Subscription\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{
    /**
     * @var array
     */
    protected $_map = [
        'fields' => [
            'customer_id' => 'main_table.customer_id'
        ]
    ];

    /**
     * Initialize select
     *
     * @return Collection|void
     */
    protected function _initSelect()
    {
        $this->getSelect()
            ->joinLeft(
                ['orders' => $this->getTable('sales_order')],
                'main_table.original_order_id = orders.entity_id',
                'increment_id'
            )->joinLeft(
                ['customers' => $this->getTable('customer_entity')],
                'main_table.customer_id = customers.entity_id',
                ['email', 'firstname', 'lastname']
            );
        return parent::_initSelect();
    }
}
