<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model\Payment;

class PaymentMethodTypeResolver implements PaymentMethodTypeResolverInterface
{
    /**
     * @var array
     */
    private $cardPaymentMethods;

    /**
     * @var array
     */
    private $paypalPaymentMethods;

    /**
     * PaymentMethodTypeResolver constructor
     *
     * @param array $cardPaymentMethods
     * @param array $paypalPaymentMethods
     */
    public function __construct(
        array $cardPaymentMethods = [],
        array $paypalPaymentMethods = []
    ) {
        $this->cardPaymentMethods = $cardPaymentMethods;
        $this->paypalPaymentMethods = $paypalPaymentMethods;
    }

    /**
     * Return 'type' of payment based off method code
     *
     * @param string $paymentMethodCode
     * @return string|null
     */
    public function execute(
        string $paymentMethodCode
    ): ?string {
        if (in_array(
            $paymentMethodCode,
            $this->cardPaymentMethods
        )) {
            return self::TYPE_CARD;
        }
        if (in_array(
            $paymentMethodCode,
            $this->paypalPaymentMethods
        )) {
            return self::TYPE_PAYPAL;
        }
        return null;
    }
}
