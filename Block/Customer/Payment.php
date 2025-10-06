<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Block\Customer;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\CustomerTokenManagement;
use PayPal\Braintree\Gateway\Config\Config as BraintreeConfig;
use PayPal\Subscription\Api\SubscriptionRepositoryInterface;

/**
 * @api
 * @since 100.0.2
 */
class Payment extends Template
{
    /**
     * @var CustomerTokenManagement
     */
    private $customerTokenManagement;

    /**
     * @var BraintreeConfig
     */
    private $braintreeConfig;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var SubscriptionRepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * Payment constructor.
     *
     * @param Context $context
     * @param CustomerTokenManagement $customerTokenManagement
     * @param BraintreeConfig $braintreeConfig
     * @param SerializerInterface $serializer
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerTokenManagement $customerTokenManagement,
        BraintreeConfig $braintreeConfig,
        SerializerInterface $serializer,
        SubscriptionRepositoryInterface $subscriptionRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerTokenManagement = $customerTokenManagement;
        $this->braintreeConfig = $braintreeConfig;
        $this->serializer = $serializer;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * Get subscription id
     *
     * @return int
     */
    public function getSubscriptionId(): int
    {
        return (int) $this->getRequest()->getParam('id');
    }

    /**
     * Get payment methods
     *
     * @return PaymentTokenInterface[]
     */
    public function getPaymentMethods(): array
    {
        return $this->customerTokenManagement->getCustomerSessionTokens();
    }

    /**
     * Get payment methods json
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getPaymentMethodsJson(): string
    {
        $subscription = $this->subscriptionRepository->getById($this->getSubscriptionId());
        $subscriptionPaymentData = $this->serializer->unserialize($subscription->getPaymentData());

        $methods = [];

        foreach ($this->getPaymentMethods() as $paymentMethod) {
            $tokenDetails = $this->serializer->unserialize($paymentMethod->getTokenDetails());
            $paymentType = $paymentMethod->getType();

            $value = [
                'paymentType' => $paymentType === 'account' ? __('Paypal Account') : $paymentType,
                'publicHash' => $paymentMethod->getPublicHash(),
                'id' => $paymentMethod->getEntityId()
            ];

            if ($paymentMethod->getType() === 'card') {
                $value['masked'] = $tokenDetails['maskedCC'];
                $value['cardType'] = $tokenDetails['type'];
                $value['expires'] = $tokenDetails['expirationDate'];
            }

            if ($subscriptionPaymentData['public_hash'] === $paymentMethod->getPublicHash()) {
                $value['is_current_method'] = true;
            }

            $methods[] = $value;
        }

        return $this->serializer->serialize($methods);
    }

    /**
     * Get braintree environment
     *
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getEnvironment(): string
    {
        return $this->braintreeConfig->getEnvironment();
    }

    /**
     * Get braintree client token
     *
     * @return string
     */
    public function getClientToken(): string
    {
        return $this->getBaseUrl() . 'rest/V1/subscription/braintree/token/client';
    }

    /**
     * Get back url
     *
     * @return string
     */
    public function getBackUrl(): string
    {
        return $this->getUrl('subscriptions/customer/view/', ['id' => $this->getSubscriptionId()]);
    }
}
