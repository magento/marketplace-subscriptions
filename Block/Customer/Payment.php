<?php

declare(strict_types=1);

namespace PayPal\Subscription\Block\Customer;

use Magento\Braintree\Gateway\Config\Config as BraintreeConfig;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\CustomerTokenManagement;
use Magento\Framework\Serialize\SerializerInterface;
use PayPal\Subscription\Api\SubscriptionRepositoryInterface;

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
     * @return int
     */
    public function getSubscriptionId(): int
    {
        return (int) $this->getRequest()->getParam('id');
    }

    /**
     * @return PaymentTokenInterface[]
     */
    public function getPaymentMethods(): array
    {
        return $this->customerTokenManagement->getCustomerSessionTokens();
    }

    /**
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
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getEnvironment(): string
    {
        return $this->braintreeConfig->getEnvironment();
    }

    /**
     * @return string
     */
    public function getClientToken(): string
    {
        return $this->getBaseUrl() . 'rest/V1/subscription/braintree/token/client';
    }

    /**
     * @return string
     */
    public function getBackUrl(): string
    {
        return $this->getUrl('subscriptions/customer/view/', ['id' => $this->getSubscriptionId()]);
    }
}
