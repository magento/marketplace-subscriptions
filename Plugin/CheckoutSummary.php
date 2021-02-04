<?php

declare(strict_types=1);

namespace PayPal\Subscription\Plugin;

use Magento\Checkout\Model\DefaultConfigProvider;
use Magento\Quote\Model\Quote\Item;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;

class CheckoutSummary
{
    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    public function __construct(SubscriptionHelper $subscriptionHelper)
    {
        $this->subscriptionHelper = $subscriptionHelper;
    }

    /**
     * @param DefaultConfigProvider $subject
     * @param array $result
     * @return array
     */
    public function afterGetConfig(DefaultConfigProvider $subject, array $result)
    {
        foreach ($result['quoteData']['items'] as $k => $item) {
            /** @var Item $item */
            if ($item->getOptionByCode(SubscriptionHelper::IS_SUBSCRIPTION) &&
                $item->getOptionByCode(SubscriptionHelper::FREQ_OPT_INTERVAL)
            ) {
                $isSubscription = $item->getOptionByCode(SubscriptionHelper::IS_SUBSCRIPTION)->getValue();
                $interval = $item->getOptionByCode(SubscriptionHelper::FREQ_OPT_INTERVAL)->getValue();
                $label = $this->subscriptionHelper->getIntervalLabel(
                    (int) $item->getProduct()->getId(),
                    (int) $interval
                );

                $result['quoteItemData'][$k][SubscriptionHelper::IS_SUBSCRIPTION] = $isSubscription;
                $result['quoteItemData'][$k][SubscriptionHelper::FREQ_OPT_INTERVAL] = $interval;
                $result['quoteItemData'][$k][SubscriptionHelper::FREQ_OPT_INTERVAL_LABEL] = $label;
            }
        }

        return $result;
    }
}
