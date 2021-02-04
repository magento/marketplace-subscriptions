<?php

declare(strict_types=1);

namespace PayPal\Subscription\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PayPal\Subscription\Api\SubscriptionManagementInterface;
use PayPal\Subscription\Api\SubscriptionRepositoryInterface;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;

class CreateSubscriptionObserver implements ObserverInterface
{
    /**
     * @var SubscriptionManagementInterface
     */
    private $subscriptionManagement;

    /**
     * @var SubscriptionRepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * CreateSubscriptionObserver constructor.
     *
     * @param SubscriptionManagementInterface $subscriptionManagement
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     * @param SubscriptionHelper $subscriptionHelper
     */
    public function __construct(
        SubscriptionManagementInterface $subscriptionManagement,
        SubscriptionRepositoryInterface $subscriptionRepository,
        SubscriptionHelper $subscriptionHelper
    ) {
        $this->subscriptionManagement = $subscriptionManagement;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->subscriptionHelper = $subscriptionHelper;
    }

    /**
     * @param Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        if (!$this->subscriptionHelper->isActive()) {
            return;
        }

        /** @var Payment $payment */
        $payment = $observer->getEvent()->getPayment();

        if (!$payment) {
            return;
        }

        /** @var Order $order */
        $order = $payment->getOrder();

        try {
            $this->subscriptionRepository->getByOrderId((int)$order->getId());
            return;
        } catch (NoSuchEntityException $e) {
            /** @var OrderItemInterface[] $items */
            $items = $order->getItems();

            /** @var Item $item */
            foreach ($items as $item) {
                $isSubscription = $item->getProductOptionByCode(SubscriptionHelper::IS_SUBSCRIPTION);

                if ($isSubscription) {
                    // Both values stored in default 'info_buyRequest' option
                    $requestOption = $item->getProductOptionByCode('info_buyRequest');

                    $frequency = isset($requestOption['frequency_option']) ?
                        (int) $requestOption['frequency_option'] :
                        (int) $item->getProductOptionByCode(SubscriptionHelper::FREQ_OPT_INTERVAL);

                    $frequencyProfileId = isset($requestOption['frequency_profile']) ?
                        (int) $requestOption['frequency_profile'] :
                        null;

                    try {
                        $this->subscriptionManagement->createSubscriptionWithItem(
                            $order,
                            $item,
                            $frequency,
                            $frequencyProfileId
                        );
                    } catch (AlreadyExistsException $exception) {
                        throw new LocalizedException(
                            __('Unable to create subscription at this time. %1', $exception->getMessage())
                        );
                    }
                }
            }
        }
    }
}
