<?php
declare(strict_types=1);

namespace PayPal\Subscription\ViewModel;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use PayPal\Subscription\Api\Data\SubscriptionInterface;

class PaymentDetails implements ArgumentInterface
{
    /**
     * @var Repository
     */
    private $assetRepository;

    /**
     * @var PaymentTokenManagementInterface
     */
    private $paymentTokenManagement;

    /**
     * @var PaymentTokenInterface[]
     */
    private $paymentTokens;

    /**
     * PaymentDetails constructor
     *
     * @param PaymentTokenManagementInterface $paymentTokenManagement
     * @param Repository $assetRepository
     */
    public function __construct(
        PaymentTokenManagementInterface $paymentTokenManagement,
        Repository $assetRepository
    ) {
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->assetRepository = $assetRepository;
        $this->paymentTokens = [];
    }

    /**
     * Return Payment Type for Susbcription
     *
     * @param SubscriptionInterface $subscription
     * @return string|null
     */
    public function getPaymentType(
        SubscriptionInterface $subscription
    ): ?string {
        $paymentToken = $this->getPaymentToken($subscription);
        return $paymentToken !== null ?
            $paymentToken->getType() :
            null;
    }

    /**
     * Return Payment Card type if possible
     *
     * @param SubscriptionInterface $subscription
     * @return string|null
     */
    public function getPaymentCardType(
        SubscriptionInterface $subscription
    ): ?string {
        $paymentToken = $this->getPaymentToken($subscription);
        if ($paymentToken === null) {
            return null;
        }
        $tokenDetails = $paymentToken->getTokenDetails() ?: '';
        $tokenDetails = json_decode($tokenDetails, true);
        return $tokenDetails['type'] ?? null;
    }

    /**
     * Return Masked Card Number if possible
     *
     * @param SubscriptionInterface $subscription
     * @return string|null
     */
    public function getMaskedCardNumber(
        SubscriptionInterface $subscription
    ): ?string {
        $paymentToken = $this->getPaymentToken($subscription);
        if ($paymentToken === null) {
            return null;
        }
        $tokenDetails = $paymentToken->getTokenDetails() ?: '';
        $tokenDetails = json_decode($tokenDetails, true);
        return $tokenDetails['maskedCC'] ?? null;
    }

    /**
     * Return Card Expiry if possible
     *
     * @param SubscriptionInterface $subscription
     * @return string|null
     */
    public function getCardExpiry(
        SubscriptionInterface $subscription
    ): ?string {
        $paymentToken = $this->getPaymentToken($subscription);
        if ($paymentToken === null) {
            return null;
        }
        $tokenDetails = $paymentToken->getTokenDetails() ?: '';
        $tokenDetails = json_decode($tokenDetails, true);
        return $tokenDetails['expirationDate'] ?? null;
    }

    /**
     * Return Card Icon URL if possible
     *
     * @param SubscriptionInterface $subscription
     * @return string|null
     */
    public function getPaymentIcon(
        SubscriptionInterface $subscription
    ): ?string {
        $cardType = $this->getPaymentCardType($subscription);
        if ($cardType === null) {
            return null;
        }
        $iconPath = 'PayPal_Subscription::images/icons/' . $cardType . '.png';
        $iconUrl = $this->assetRepository->getUrl($iconPath);
        return $iconUrl;
    }

    /**
     * Return Payment token for Subscription payment
     *
     * @param SubscriptionInterface $subscription
     * @return PaymentTokenInterface|null
     */
    private function getPaymentToken(
        SubscriptionInterface $subscription
    ): ?PaymentTokenInterface {
        $paymentDetails = $subscription->getPaymentData() ?: '';
        $paymentDetails = json_decode($paymentDetails, true);
        $paymentPublicHash = $paymentDetails['public_hash'] ?? null;
        if ($paymentPublicHash === null) {
            return null;
        }
        if (!isset($this->paymentTokens[$paymentPublicHash])) {
            /** @var PaymentTokenInterface $paymentToken */
            $this->paymentTokens[$paymentPublicHash] = $this->paymentTokenManagement->getByPublicHash(
                $paymentPublicHash,
                $subscription->getCustomerId()
            );
        }
        return $this->paymentTokens[$paymentPublicHash];
    }
}
