<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PayPal\Subscription\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Controller\RegistryConstants;
use PayPal\Subscription\Api\Data\SubscriptionInterface;
use PayPal\Subscription\Block\Adminhtml\Customer\Subscriptions\Grid\Column\Renderer\Item;
use PayPal\Subscription\Block\Adminhtml\Customer\Subscriptions\Grid\Column\Renderer\Status;
use PayPal\Subscription\Model\ResourceModel\Subscription\Collection;
use PayPal\Subscription\Model\ResourceModel\Subscription\CollectionFactory;

/**
 * @api
 * @since 100.0.2
 */
class Subscriptions extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * Subscriptions constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param CustomerRepositoryInterface $customerRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        CustomerRepositoryInterface $customerRepository,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->collectionFactory = $collectionFactory;
        $this->customerRepository = $customerRepository;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customer_subscriptions_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
    }

    /**
     * Apply various selection filters to prepare the sales order grid collection.
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $customerId = $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        try {
            $customer = $this->customerRepository->getById($customerId);
            /** @var Collection $collection */
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter(
                SubscriptionInterface::CUSTOMER_ID,
                $customerId
            );
            $this->setCollection($collection);
        } catch (\Exception $e) {
            return parent::_prepareCollection();
        }
        return parent::_prepareCollection();
    }

    /**
     * @inheritdoc
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            SubscriptionInterface::SUBSCRIPTION_ID,
            [
                'header' => __('Subscription #'),
                'width' => '100',
                'index' => SubscriptionInterface::SUBSCRIPTION_ID
            ]
        );
        $this->addColumn(
            SubscriptionInterface::CREATED_AT,
            [
                'header' => __('Created'),
                'index' => SubscriptionInterface::CREATED_AT,
                'type' => 'datetime'
            ]
        );
        $this->addColumn(
            SubscriptionInterface::NEXT_RELEASE_DATE,
            [
                'header' => __('Next Release'),
                'index' => SubscriptionInterface::NEXT_RELEASE_DATE,
                'type' => 'datetime'
            ]
        );
        $this->addColumn(
            SubscriptionInterface::PREV_RELEASE_DATE,
            [
                'header' => __('Previous Release'),
                'index' => SubscriptionInterface::PREV_RELEASE_DATE,
                'type' => 'datetime'
            ]
        );
        $this->addColumn(
            'item',
            [
                'header' => __('Item'),
                'renderer' => Item::class
            ]
        );
        $this->addColumn(
            SubscriptionInterface::SHIPPING_METHOD,
            [
                'header' => __('Shipping Method'),
                'index' => SubscriptionInterface::SHIPPING_METHOD
            ]
        );
        $this->addColumn(
            SubscriptionInterface::PAYMENT_METHOD,
            [
                'header' => __('Payment Method'),
                'index' => SubscriptionInterface::PAYMENT_METHOD
            ]
        );
        $this->addColumn(
            SubscriptionInterface::STATUS,
            [
                'header' => __('Status'),
                'index' => SubscriptionInterface::STATUS,
                'renderer' => Status::class
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Retrieve the Url for a specified sales order row.
     *
     * @param \PayPal\Subscription\Model\Subscription|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'paypal_subscription/subscriptions/edit',
            ['id' => $row->getId()]
        );
    }

    /**
     * @inheritdoc
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'paypal_subscription/customer/subscriptions',
            ['_current' => true]
        );
    }
}
