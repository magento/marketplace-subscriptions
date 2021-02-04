<?php

declare(strict_types=1);

namespace PayPal\Subscription\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item;
use PayPal\Subscription\Helper\Data;

class PaymentActiveObserver implements ObserverInterface
{
    /**
     * @var array
     */
    private $acceptedMethods = ['braintree'];

    private $hasSubscriptionItem = false;

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $result = $observer->getEvent()->getResult();
        $method_instance = $observer->getEvent()->getMethodInstance();
        $quote = $observer->getEvent()->getQuote();
        $quoteItems = $quote->getAllItems();

        /** @var Item $item */
        foreach ($quoteItems as $item) {
            if ($item->getOptionByCode(Data::IS_SUBSCRIPTION)) {
                $this->hasSubscriptionItem = true;
            }
        }

        if ($this->hasSubscriptionItem && !in_array($method_instance->getCode(), $this->acceptedMethods, true)) {
            $result->setData('is_available', false);
        }
    }
}
