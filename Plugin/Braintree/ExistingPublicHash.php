<?php
declare(strict_types=1);

namespace PayPal\Subscription\Plugin\Braintree;

use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface as Subject;
use Psr\Log\LoggerInterface;

class ExistingPublicHash
{
    private const PAYMENT_METHOD_CODES = [
        'braintree',
        'braintree_paypal'
    ];

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Error occurs when the vaulted token has been saved with token details including customer ID:
     *
     * @see \PayPal\Braintree\Model\Adapter\PaymentMethod\BraintreePaymentTokenAdapter::getTokenDetails
     *
     * During order placement, Braintree responds with gateway token information which is then persisted to vault token.
     * During the order process, the public hash is then generated dynamically and stored against this token.
     *
     * When the public hash for this token is generated dynamically, the customer ID is NOT included in the token detail
     *
     * @see \PayPal\Braintree\Gateway\Response\VaultDetailsHandler::getVaultPaymentToken
     *
     * This results in a different public hash value being generated for an existing gateway token.
     * Fix here ensures we use existing public hash for stored token during save.
     *
     * @param Subject $subject
     * @param PaymentTokenInterface $token
     * @param OrderPaymentInterface $payment
     * @return array
     */
    public function beforeSaveTokenWithPaymentLink(
        Subject $subject,
        PaymentTokenInterface $token,
        OrderPaymentInterface $payment
    ): array {
        try {
            $existingToken = $subject->getByGatewayToken(
                $token->getGatewayToken(),
                $token->getPaymentMethodCode(),
                $token->getCustomerId()
            );
            if ($existingToken instanceof PaymentTokenInterface &&
                $existingToken->getPublicHash() !== $token->getPublicHash() &&
                $this->tokensAreBraintree($token, $existingToken) === true
            ) {
                $token->setPublicHash($existingToken->getPublicHash());
            }
        } catch (\Throwable $exception) {
            $this->logger->error("An error occurred whilst setting existing public hash to current token", [
                'exception_message' => $exception->getMessage(),
            ]);
        }
        return [$token, $payment];
    }

    /**
     * Check tokens are either braintree or braintree_paypal
     *
     * @param PaymentTokenInterface $token
     * @param PaymentTokenInterface $existingToken
     * @return bool
     */
    private function tokensAreBraintree(
        PaymentTokenInterface $token,
        PaymentTokenInterface $existingToken
    ): bool {
        return $token->getPaymentMethodCode() === $existingToken->getPaymentMethodCode()
            && in_array($token->getPaymentMethodCode(), self::PAYMENT_METHOD_CODES);
    }
}
