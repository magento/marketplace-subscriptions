<?php

declare(strict_types=1);

namespace PayPal\Subscription\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class ContainsSubscriptionObserver implements ObserverInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * ContainsSubscriptionObserver constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param Observer $observer
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();

        /** @var Item $item */
        foreach ($order->getItems() as $item) {
            $options = $item->getProductOptions();
            if (array_key_exists('is_subscription', $options)) {
                $order->setContainsSubscription(true);
                $this->orderRepository->save($order);
                break;
            }
        }
    }
}
