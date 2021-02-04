<?php

declare(strict_types=1);

namespace PayPal\Subscription\Observer;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\Quote\Item;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;
use PayPal\Subscription\Model\FrequencyProfileRepository;

class CartProductAddAfterObserver implements ObserverInterface
{
    /**
     * @var Http
     */
    private $request;

    /**
     * @var FrequencyProfileRepository
     */
    private $frequencyProfileRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * CartProductObserver constructor.
     *
     * @param Http $request
     * @param FrequencyProfileRepository $frequencyProfileRepository
     * @param SerializerInterface $serializer
     * @param SubscriptionHelper $subscriptionHelper
     */
    public function __construct(
        Http $request,
        FrequencyProfileRepository $frequencyProfileRepository,
        SerializerInterface $serializer,
        SubscriptionHelper $subscriptionHelper
    ) {
        $this->request = $request;
        $this->frequencyProfileRepository = $frequencyProfileRepository;
        $this->serializer = $serializer;
        $this->subscriptionHelper = $subscriptionHelper;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        if (!$this->subscriptionHelper->isActive()) {
            return;
        }

        $frequencyProfileId = $this->request->getPost('frequency_profile');
        $interval = $this->request->getPost('frequency_option');

        if ((!$frequencyProfileId && !$interval) || (int) $interval === 0) {
            return;
        }

        $frequencyProfile = $this->frequencyProfileRepository->getById((int) $frequencyProfileId);
        $frequencyOptions = $this->serializer->unserialize($frequencyProfile->getFrequencyOptions());
        $validInterval = array_search($interval, array_column($frequencyOptions, 'interval'), true);

        if ($validInterval !== null) {
            /** @var Item $item */
            $item = $observer->getEvent()->getData('quote_item');
            $item = $item->getParentItem() ?? $item;

            // Product subscription pricing
            $product = $item->getProduct();
            $priceType = $product->getData(SubscriptionHelper::SUB_PRICE_TYPE) !== null
                ? (int) $product->getData(SubscriptionHelper::SUB_PRICE_TYPE)
                : null;
            $priceValue = $product->getData(SubscriptionHelper::SUB_PRICE_VALUE) !== null
                ? (float) $product->getData(SubscriptionHelper::SUB_PRICE_VALUE)
                : null;

            if ($priceType === SubscriptionHelper::FIXED_PRICE) {
                $item->setCustomPrice($priceValue);
                $item->setOriginalCustomPrice($priceValue);
            } elseif ($priceType === SubscriptionHelper::DISCOUNT_PRICE) {
                $discountedPrice = $this->subscriptionHelper->getDiscountedPrice(
                    $priceValue,
                    (float) $product->getPrice()
                );
                $item->setCustomPrice($discountedPrice);
                $item->setOriginalCustomPrice($discountedPrice);
            }

            // Set item options to be picked up in cartData
            // An array can validly be passed into this method. The core method docblock is incorrect.
            $item->addOption(
                [
                    'product_id' => $item->getProductId(),
                    'code' => SubscriptionHelper::FREQ_OPT_INTERVAL,
                    'value' => $interval
                ]
            );

            $item->addOption(
                [
                    'product_id' => $item->getProductId(),
                    'code' => SubscriptionHelper::IS_SUBSCRIPTION,
                    'value' => true
                ]
            );
        }
    }
}
