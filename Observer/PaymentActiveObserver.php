<?php
declare(strict_types=1);

namespace PayPal\Subscription\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item;
use PayPal\Subscription\Helper\Data;
use PayPal\Subscription\Model\ConfigurationInterface;

class PaymentActiveObserver implements ObserverInterface
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var bool
     */
    private $hasSubscriptionItem = false;

    /**
     * PaymentActiveObserver constructor
     *
     * @param ConfigurationInterface $configuration
     */
    public function __construct(
        ConfigurationInterface $configuration
    ) {
        $this->configuration = $configuration;
    }

    /**
     * Get active subscription payment
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        if (!$this->configuration->getActive()) {
            return;
        }
        $quote = $observer->getEvent()->getQuote();
        if (!$quote) {
            return;
        }

        $result = $observer->getEvent()->getResult();
        $method_instance = $observer->getEvent()->getMethodInstance();
        $quoteItems = $quote->getAllItems();

        /** @var Item $item */
        foreach ($quoteItems as $item) {
            if ($item->getOptionByCode(Data::IS_SUBSCRIPTION)) {
                $this->hasSubscriptionItem = true;
            }
        }
        $acceptedMethods = $this->configuration->getAllowedPaymentMethods();
        $acceptedMethodsArray = explode(',', $acceptedMethods ?? '');
        if ($this->hasSubscriptionItem && !in_array($method_instance->getCode(), $acceptedMethodsArray, true)) {
            $result->setData('is_available', false);
        }
    }
}
