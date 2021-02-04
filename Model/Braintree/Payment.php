<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model\Braintree;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Model\PaymentTokenFactory;
use PayPal\Subscription\Api\BraintreeInterface;
use PayPal\Subscription\Api\BraintreePaymentInterface;
use PayPal\Subscription\Api\Data\SubscriptionInterface;
use PayPal\Subscription\Api\SubscriptionRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;

class Payment implements BraintreePaymentInterface
{
    /**
     * @var SubscriptionRepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var BraintreeInterface
     */
    private $braintree;

    /**
     * @var PaymentTokenFactoryInterface
     */
    private $paymentTokenFactory;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * Payment constructor.
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     * @param BraintreeInterface $braintree
     * @param PaymentTokenFactoryInterface $paymentTokenFactory
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     * @param EncryptorInterface $encryptor
     * @param SerializerInterface $serializer
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        SubscriptionRepositoryInterface $subscriptionRepository,
        BraintreeInterface $braintree,
        PaymentTokenFactoryInterface $paymentTokenFactory,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        EncryptorInterface $encryptor,
        SerializerInterface $serializer,
        ManagerInterface $messageManager
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->braintree = $braintree;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->encryptor = $encryptor;
        $this->serializer = $serializer;
        $this->messageManager = $messageManager;
    }

    /**
     * @param int $customerId
     * @param int $subscriptionId
     * @param string $nonce
     * @param string $paymentType
     * @return SubscriptionInterface
     * @throws LocalizedException
     */
    public function changePaymentMethodNew(
        int $customerId,
        int $subscriptionId,
        string $nonce,
        string $paymentType
    ): SubscriptionInterface {
        try {
            $subscription = $this->subscriptionRepository->getCustomerSubscription($customerId, $subscriptionId);
            $braintreeCustomerId = $this->braintree->getBraintreeCustomerId($subscription);
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('Could not find subscription'));
        }

        // Store in Braintree Vault
        $paymentMethodResult = $this->braintree->storeInVault($nonce, $braintreeCustomerId);

        // Store in Magento Vault
        $vaultPaymentToken = $this->paymentTokenFactory->create(PaymentTokenFactory::TOKEN_TYPE_CREDIT_CARD);
        $vaultPaymentToken->setCustomerId($customerId);
        $vaultPaymentToken->setPaymentMethodCode($paymentType);

        if ($paymentType === 'braintree') {
            // Credit Card
            $vaultPaymentToken->setType(PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD);
            $vaultPaymentToken->setTokenDetails($this->serializer->serialize([
                'type' => $paymentMethodResult->paymentMethod->cardType,
                'maskedCC' => $paymentMethodResult->paymentMethod->last4,
                'expirationDate' => sprintf(
                    '%s/%s',
                    $paymentMethodResult->paymentMethod->expirationMonth,
                    $paymentMethodResult->paymentMethod->expirationYear
                )
            ]));
        } elseif ($paymentType === 'braintree_paypal') {
            // PayPal
            $vaultPaymentToken->setType(PaymentTokenFactoryInterface::TOKEN_TYPE_ACCOUNT);
            $vaultPaymentToken->setTokenDetails($this->serializer->serialize([
                'payerEmail' => $paymentMethodResult->paymentMethod->email,
                'billingAgreementId' => $paymentMethodResult->paymentMethod->billingAgreementId
            ]));
        }
        $vaultPaymentToken->setExpiresAt(
            sprintf(
                '%s-%s-01 00:00:00',
                $paymentMethodResult->paymentMethod->expirationYear ?? date('Y', strtotime('+1 year')),
                $paymentMethodResult->paymentMethod->expirationMonth ?? date('m')
            )
        );
        $vaultPaymentToken->setGatewayToken($paymentMethodResult->paymentMethod->token);
        $vaultPaymentToken->setPublicHash(
            $this->encryptor->getHash(
                $customerId
                . $vaultPaymentToken->getPaymentMethodCode()
                . $vaultPaymentToken->getType()
                . $vaultPaymentToken->getTokenDetails()
            )
        );

        try {
            $this->paymentTokenRepository->save($vaultPaymentToken);
        } catch (AlreadyExistsException $e) {
            $this->messageManager->addErrorMessage(__('This payment already exists.'));
            throw new LocalizedException(__('Payment already exists.'));
        }

        // Save new payment data to subscription
        $subscription->setPaymentMethod($paymentType);
        $subscription->setPaymentData(
            $this->serializer->serialize(
                [
                    'gateway_token' => $vaultPaymentToken->getGatewayToken(),
                    'public_hash' => $vaultPaymentToken->getPublicHash()
                ]
            )
        );
        try {
            $this->subscriptionRepository->save($subscription);

            $subscription->addHistory(
                'New Payment Method',
                'customer',
                sprintf(
                    'A new %s payment method was added',
                    ($paymentType === 'braintree' ? 'Credit Card' : 'PayPal')
                )
            )->save();
            $this->messageManager->addSuccessMessage(__('A new payment method was added.'));
        } catch (AlreadyExistsException $e) {
            $this->messageManager->addErrorMessage(__('Could not save new payment method.'));
            throw new LocalizedException(__('Could not save new payment method'));
        }

        return $subscription;
    }
}
