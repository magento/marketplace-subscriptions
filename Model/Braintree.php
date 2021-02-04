<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Braintree\PaymentMethod;
use PayPal\Braintree\Gateway\Config\Config as BraintreeConfig;
use PayPal\Braintree\Model\Adapter\BraintreeAdapter;
use PayPal\Braintree\Model\Adminhtml\Source\Environment;
use PayPal\Braintree\Model\Ui\ConfigProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use PayPal\Subscription\Api\BraintreeInterface;
use PayPal\Subscription\Api\Data\BraintreeDataInterface;
use PayPal\Subscription\Api\Data\BraintreeDataInterfaceFactory;
use PayPal\Subscription\Api\SubscriptionRepositoryInterface;

class Braintree implements BraintreeInterface
{
    private const PAYMENT_CONFIG_PREFIX = 'payment/braintree/';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var BraintreeDataInterfaceFactory
     */
    private $braintreeDataFactory;

    /**
     * @var SubscriptionRepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var BraintreeAdapter
     */
    private $adapter;

    /**
     * Braintree constructor.
     *
     * @param ConfigProvider $configProvider
     * @param BraintreeDataInterfaceFactory $braintreeDataFactory
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     * @param SerializerInterface $serializer
     * @param ScopeConfigInterface $scopeConfig
     * @param BraintreeAdapter $adapter
     */
    public function __construct(
        ConfigProvider $configProvider,
        BraintreeDataInterfaceFactory $braintreeDataFactory,
        SubscriptionRepositoryInterface $subscriptionRepository,
        SerializerInterface $serializer,
        ScopeConfigInterface $scopeConfig,
        BraintreeAdapter $adapter
    ) {
        $this->configProvider = $configProvider;
        $this->braintreeDataFactory = $braintreeDataFactory;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->serializer = $serializer;
        $this->scopeConfig = $scopeConfig;
        $this->adapter = $adapter;
    }

    /**
     * @return BraintreeDataInterface
     */
    public function getClientToken(): BraintreeDataInterface
    {
        $response = $this->braintreeDataFactory->create();

        try {
            $clientToken = $this->configProvider->getClientToken();

            if (!$clientToken) {
                $response->setError('Unable to get client token.');
            } else {
                $response->setToken($clientToken);
            }

        } catch (InputException | NoSuchEntityException $e) {
            $response->setError($e->getMessage());
        }

        return $response;
    }

    /**
     * @param $subscription
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBraintreeCustomerId($subscription)
    {
        /** @var Subscription $subscription */
        $subscription = $this->subscriptionRepository->getById((int) $subscription->getId());
        $paymentData = $this->serializer->unserialize($subscription->getPaymentData());
        $braintreeData = PaymentMethod::find($paymentData['gateway_token']);

        if (isset($braintreeData->customerId)) {
            return $braintreeData->customerId;
        }
    }

    /**
     * @param $nonce
     * @param $braintreeCustomerId
     * @return mixed
     */
    public function storeInVault($nonce, $braintreeCustomerId)
    {
        $this->initCredentials();

        $paymentMethod = [
            'paymentMethodNonce' => $nonce,
            'customerId' => $braintreeCustomerId
        ];

        return PaymentMethod::create($paymentMethod);
    }

    private function initCredentials()
    {
        if ($this->scopeConfig->getValue(
            self::PAYMENT_CONFIG_PREFIX . BraintreeConfig::KEY_ENVIRONMENT
        ) === Environment::ENVIRONMENT_PRODUCTION
        ) {
            $this->adapter->environment(Environment::ENVIRONMENT_PRODUCTION);
        } else {
            $this->adapter->environment(Environment::ENVIRONMENT_SANDBOX);
        }

        $this->adapter->merchantId(
            $this->scopeConfig->getValue(self::PAYMENT_CONFIG_PREFIX . BraintreeConfig::KEY_MERCHANT_ID)
        );
        $this->adapter->publicKey(
            $this->scopeConfig->getValue(self::PAYMENT_CONFIG_PREFIX . BraintreeConfig::KEY_PUBLIC_KEY)
        );
        $this->adapter->privateKey(
            $this->scopeConfig->getValue(self::PAYMENT_CONFIG_PREFIX . BraintreeConfig::KEY_PRIVATE_KEY)
        );
    }
}
