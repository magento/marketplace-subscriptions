<?php

declare(strict_types=1);

namespace PayPal\Subscription\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;

class CheckCustomerLoginObserver implements ObserverInterface
{
    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * CheckCustomerLoginObserver constructor.
     *
     * @param SubscriptionHelper $subscriptionHelper
     */
    public function __construct(SubscriptionHelper $subscriptionHelper)
    {
        $this->subscriptionHelper = $subscriptionHelper;
    }

    /**
     * @param Observer $observer
     * @return CheckCustomerLoginObserver
     */
    public function execute(Observer $observer): CheckCustomerLoginObserver
    {
        if (!$this->subscriptionHelper->isActive()) {
            return $this;
        }

        /* @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        $result = $observer->getEvent()->getResult();

        foreach ($quote->getAllItems() as $item) {
            /** @var Item $item */
            if ($item->getOptionByCode(SubscriptionHelper::IS_SUBSCRIPTION)
                && (int) $item->getOptionByCode(SubscriptionHelper::IS_SUBSCRIPTION)->getValue() === 1) {
                $result->setIsAllowed(false);
                break;
            }
        }

        return $this;
    }
}
