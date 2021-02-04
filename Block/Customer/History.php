<?php

declare(strict_types=1);

namespace PayPal\Subscription\Block\Customer;

use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Theme\Block\Html\Pager;
use PayPal\Subscription\Api\Data\SubscriptionHistoryInterface;
use PayPal\Subscription\Model\ResourceModel\SubscriptionHistory\Collection;
use PayPal\Subscription\Model\ResourceModel\SubscriptionHistory\CollectionFactory as SubscriptionHistoryCollection;

class History extends Template
{
    /**
     * @var SubscriptionHistoryCollection
     */
    private $subscriptionHistoryCollection;

    /**
     * History constructor.
     *
     * @param Context $context
     * @param SubscriptionHistoryCollection $subscriptionHistoryCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        SubscriptionHistoryCollection $subscriptionHistoryCollection,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->subscriptionHistoryCollection = $subscriptionHistoryCollection;
    }

    /**
     * @return $this|Template
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getSubscriptionHistory()) {
            $pager = $this->getLayout()
                ->createBlock(Pager::class, 'subscription.customer.pager.history')
                ->setAvailableLimit([5 => 5, 10 => 10, 15 => 15, 20 => 20])
                ->setShowPerPage(true)
                ->setCollection(
                    $this->getSubscriptionHistory()
                );
            $this->setChild('pager', $pager);
            $this->getSubscriptionHistory()->load();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return Collection
     */
    public function getSubscriptionHistory(): Collection
    {
        $subscriptionId = $this->getRequest()->getParam('id');

        $page = $this->getRequest()->getParam('p') ?: 1;
        $pageSize = $this->getRequest()->getParam('limit') ?: 5;

        /** @var Collection $collection */
        $collection = $this->subscriptionHistoryCollection->create();
        $collection->addFieldToFilter(SubscriptionHistoryInterface::SUBSCRIPTION_ID, ['eq' => $subscriptionId]);
        $collection->addFieldToFilter(SubscriptionHistoryInterface::VISIBLE, ['eq' => 1]);
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        $collection->setOrder(SubscriptionHistoryInterface::CREATED_AT, SortOrder::SORT_DESC);

        return $collection;
    }
}
