<?php

declare(strict_types=1);

namespace PayPal\Subscription\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Model\Order;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;

class ConvertItemOptionsObserver implements ObserverInterface
{
    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var array
     */
    private $quoteItems = [];
    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * ConvertItemOptionsObserver constructor.
     *
     * @param SubscriptionHelper $subscriptionHelper
     */
    public function __construct(SubscriptionHelper $subscriptionHelper)
    {
        $this->subscriptionHelper = $subscriptionHelper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->subscriptionHelper->isActive()) {
            return;
        }

        $this->quote = $observer->getQuote();
        $this->order = $observer->getOrder();

        foreach ($this->order->getItems() as $orderItem) {
            $orderItem = $orderItem->getParentItem() ?? $orderItem;
            $orderOptions = $orderItem->getProductOptions();
            $quoteItem = $this->getQuoteItemById($orderItem->getQuoteItemId());

            if ($quoteItem) {
                $isSubscription = $quoteItem->getOptionByCode('is_subscription');
                if ($isSubscription) {
                    $orderOptions['is_subscription'] = true;
                    $orderItem->setProductOptions($orderOptions);
                }
            }
        }
    }

    /**
     * @param $id
     * @return Item|null
     */
    private function getQuoteItemById($id): ?Item
    {
        if (empty($this->quoteItems)) {
            /* @var Item $item */
            foreach ($this->quote->getAllItems() as $item) {
                $item = $item->getParentItem() ?? $item;
                $this->quoteItems[$item->getId()] = $item;
            }
        }
        if (array_key_exists($id, $this->quoteItems)) {
            return $this->quoteItems[$id];
        }
        return null;
    }
}
