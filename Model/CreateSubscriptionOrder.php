<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\InvoiceOrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use PayPal\Subscription\Api\Data\SubscriptionInterface;

class CreateSubscriptionOrder implements CreateSubscriptionOrderInterface
{
    /**
     * @var InvoiceOrderInterface
     */
    private $invoiceOrder;

    /**
     * @var CartManagementInterface|QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * CreateSubscriptionOrder constructor
     *
     * @param InvoiceOrderInterface $invoiceOrder
     * @param CartManagementInterface $quoteManagement
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        InvoiceOrderInterface $invoiceOrder,
        CartManagementInterface $quoteManagement,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->invoiceOrder = $invoiceOrder;
        $this->quoteManagement = $quoteManagement;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Convert Subscription Quote to Order
     *
     * @param CartInterface $subscriptionQuote
     * @param SubscriptionInterface|null $subscription
     * @return OrderInterface
     * @throws CommandException
     * @throws LocalizedException
     */
    public function execute(
        CartInterface $subscriptionQuote,
        ?SubscriptionInterface $subscription = null
    ): OrderInterface {
        try {
            /** @var OrderInterface|Order $order */
            $order = $this->quoteManagement->submit($subscriptionQuote);
        } catch (\Exception $e) {
            if ($subscription !== null) {
                if ($e instanceof CommandException) {
                    $currentPaymentFailures = $subscription->getFailedPayments() ?: 0;
                    $newPaymentFailures = $currentPaymentFailures + 1;
                    $subscription->setFailedPayments($newPaymentFailures);
                }
            }
            throw $e;
        }
        if (!$order) {
            throw new LocalizedException(__(
                'Unable to create order for Subscription Quote ID %1',
                $subscriptionQuote->getId()
            ));
        }
        foreach ($order->getItems() as $orderItem) {
            // @codingStandardsIgnoreStart
            $productOptions = array_merge(
                $orderItem->getProductOptions(),
                ['is_subscription' => true]
            );
            // @codingStandardsIgnoreEnd
            $orderItem->setProductOptions($productOptions);
        }
        $order->setData(
            'is_subscription_release',
            1
        );
        $this->orderRepository->save($order);
        if ($order->canInvoice()) {
            try {
                $this->invoiceOrder->execute(
                    $order->getId(),
                    true
                );
            } catch (\Exception $e) {
                $subscription->setFailedPayments(
                    $subscription->getFailedPayments()+1
                );
            }
        }
        return $order;
    }
}
