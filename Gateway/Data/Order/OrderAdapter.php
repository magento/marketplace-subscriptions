<?php
declare(strict_types=1);

namespace PayPal\Subscription\Gateway\Data\Order;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\Order;
use PayPal\Braintree\Gateway\Data\Order\AddressAdapterFactory;
use PayPal\Braintree\Gateway\Data\Order\OrderAdapter as BraintreeOrderAdapter;

class OrderAdapter extends BraintreeOrderAdapter
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @param Order $order
     * @param CartRepositoryInterface $quoteRepository
     * @param AddressAdapterFactory $addressAdapterFactory
     */
    public function __construct(
        Order $order,
        CartRepositoryInterface $quoteRepository,
        AddressAdapterFactory $addressAdapterFactory
    ) {
        parent::__construct(
            $order,
            $quoteRepository,
            $addressAdapterFactory
        );
        $this->order = $order;
    }

    /**
     * Return boolean on whether order is a subscription release
     *
     * @return bool
     */
    public function getIsSubscriptionRelease(): bool
    {
        return (bool) $this->order->getData('is_subscription_release');
    }
}
