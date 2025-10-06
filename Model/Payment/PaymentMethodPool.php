<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model\Payment;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use PayPal\Subscription\Api\SubscriptionPaymentInterface;

class PaymentMethodPool implements PaymentMethodPoolInterface
{
    /**
     * @var SubscriptionPaymentInterface[]
     */
    private array $paymentMethodPool;

    /**
     * PaymentMethodPool constructor
     *
     * @param array $paymentMethodPool
     * @throws LocalizedException
     */
    public function __construct(
        array $paymentMethodPool = []
    ) {
        // @codingStandardsIgnoreStart
        foreach ($paymentMethodPool as $methodName => $paymentMethod) {
            if (!($paymentMethod instanceof SubscriptionPaymentInterface)) {
                throw new LocalizedException(__(
                    'Subscription Payment must implement PayPal\Subscription\Api\SubscriptionPaymentInterface - %1',
                    $methodName
                ));
            }
            $this->paymentMethodPool[$methodName] = $paymentMethod;
        }
        // @codingStandardsIgnoreEnd
    }

    /**
     * @inheritDoc
     */
    public function getAvailablePayments(): array
    {
        foreach ($this->paymentMethodPool as $methodName => $paymentMethod) {
            if (!($paymentMethod instanceof SubscriptionPaymentInterface)) {
                throw new LocalizedException(__(
                    'Subscription Payment must implement PayPal\Subscription\Api\SubscriptionPaymentInterface - %1',
                    $methodName
                ));
            }
        }
        return $this->paymentMethodPool;
    }

    /**
     * @inheritDoc
     */
    public function getByPaymentMethod(
        string $paymentMethod
    ): SubscriptionPaymentInterface {
        foreach ($this->paymentMethodPool as $methodName => $method) {
            if ($paymentMethod === $methodName) {
                return $method;
            }
        }
        throw new InputException(__(
            'No Payment exists for method %1',
            $paymentMethod
        ));
    }
}
