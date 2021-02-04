<?php

declare(strict_types=1);

namespace PayPal\Subscription\Observer;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote\Item as ItemResource;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;

/**
 * Observer to set quote item options when a product subscription interval is selected
 */
class AdminOrderProcessItemObserver implements ObserverInterface
{
    /**
     * @var ItemResource
     */
    private $itemResource;

    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * QuoteSetProductObserver constructor.
     *
     * @param ItemResource $itemResource
     * @param SubscriptionHelper $subscriptionHelper
     */
    public function __construct(
        ItemResource $itemResource,
        SubscriptionHelper $subscriptionHelper
    ) {
        $this->itemResource = $itemResource;
        $this->subscriptionHelper = $subscriptionHelper;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws AlreadyExistsException
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        /** @var Http $request */
        $request = $observer->getEvent()->getRequestModel();
        $addedItems = $request->getPost('item');
        $updateItems = $request->getPost('update_items');
        $submit = $request->getPost('Submit');

        if ($submit) {
            return;
        }

        if ($updateItems) {
            /** @var Quote $quote */
            $quote = $observer->getEvent()->getOrderCreateModel()->getQuote();
            $items = $quote->getAllItems();

            foreach ($items as $item) {
                /** @var Quote\Item $item */
                $item = $item->getParentItem() ?? $item;

                if ($addedItems[$item->getId()]['frequency_option'] === '') {
                    $item->removeOption(SubscriptionHelper::FREQ_OPT_INTERVAL);
                    $item->removeOption(SubscriptionHelper::IS_SUBSCRIPTION);
                    $item->saveItemOptions();
                    $item->setPrice($item->getProduct()->getPrice());
                    $item->setOriginalPrice($item->getProduct()->getPrice());
                    $item->setCustomPrice(null);
                    $item->setOriginalCustomPrice(null);
                    $this->itemResource->save($item);
                } else {
                    $item->addOption(
                        [
                            'product_id' => $item->getProduct()->getId(),
                            'code' => SubscriptionHelper::FREQ_OPT_INTERVAL,
                            'value' => $addedItems[$item->getId()]['frequency_option']
                        ]
                    );
                    $item->addOption(
                        [
                            'product_id' => $item->getProduct()->getId(),
                            'code' => SubscriptionHelper::IS_SUBSCRIPTION,
                            'value' => true
                        ]
                    );
                    $item->saveItemOptions();

                    $priceType = $item->getProduct()->getData(SubscriptionHelper::SUB_PRICE_TYPE) !== null
                        ? (int) $item->getProduct()->getData(SubscriptionHelper::SUB_PRICE_TYPE)
                        : null;
                    $priceValue = $item->getProduct()->getData(SubscriptionHelper::SUB_PRICE_VALUE) !== null
                        ? (float) $item->getProduct()->getData(SubscriptionHelper::SUB_PRICE_VALUE)
                        : null;

                    if ($priceType === SubscriptionHelper::FIXED_PRICE) {
                        $item->setCustomPrice($priceValue);
                        $item->setOriginalCustomPrice($priceValue);
                    } elseif ($priceType === SubscriptionHelper::DISCOUNT_PRICE) {
                        $discountedPrice = $this->subscriptionHelper->getDiscountedPrice(
                            $priceValue,
                            (float) $item->getProduct()->getPrice()
                        );
                        $item->setCustomPrice($discountedPrice);
                        $item->setOriginalCustomPrice($discountedPrice);
                    }
                    $this->itemResource->save($item);
                }
            }
        }
    }
}
