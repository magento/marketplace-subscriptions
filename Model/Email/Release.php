<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model\Email;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilderFactory;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use PayPal\Subscription\Api\Data\SubscriptionInterface;
use PayPal\Subscription\Model\ConfigurationInterface;
use PayPal\Subscription\Model\Email;
use PayPal\Subscription\Model\Email\Subscription as SubscriptionEmail;
use PayPal\Subscription\ViewModel\PaymentDetails;
use Psr\Log\LoggerInterface;

class Release extends Email
{
    public const TEMPLATE_FAILURE = 'paypal_subscriptions_email_configuration_release_failure';
    public const CONFIG_FAILURE = 'paypal_subscriptions/email_configuration/release_failure';
    public const TEMPLATE_FAILURE_ADMIN = 'paypal_subscriptions_email_configuration_release_failure_admin';
    public const CONFIG_FAILURE_ADMIN = 'paypal_subscriptions/email_configuration/release_failure_admin';
    public const TEMPLATE_REMINDER = 'paypal_subscriptions_email_configuration_release_reminder';
    public const CONFIG_REMINDER = 'paypal_subscriptions/email_configuration/release_reminder';
    public const CONFIG_PRICE_CHANGED = 'paypal_subscriptions/email_configuration/price_changed';
    public const TEMPLATE_PRICE_CHANGED = 'paypal_subscriptions_email_configuration_price_changed';

    /**
     * @var PaymentDetails
     */
    private $paymentDetails;

    /**
     * @var SubscriptionEmail
     */
    private $subscriptionEmail;

    /**
     * Release constructor.
     * @param TransportBuilderFactory $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param ManagerInterface $eventManager
     * @param LoggerInterface $logger
     * @param PaymentDetails $paymentDetails
     * @param ConfigurationInterface $configuration
     * @param Subscription $subscriptionEmail
     * @param TimezoneInterface $timezone
     * @param PricingHelper $pricingHelper
     */
    public function __construct(
        TransportBuilderFactory $transportBuilder,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ManagerInterface $eventManager,
        LoggerInterface $logger,
        PaymentDetails $paymentDetails,
        ConfigurationInterface $configuration,
        SubscriptionEmail $subscriptionEmail,
        TimezoneInterface $timezone,
        private readonly PricingHelper $pricingHelper,
    ) {
        parent::__construct(
            $transportBuilder,
            $storeManager,
            $scopeConfig,
            $eventManager,
            $logger,
            $configuration,
            $timezone
        );
        $this->paymentDetails = $paymentDetails;
        $this->subscriptionEmail = $subscriptionEmail;
    }

    /**
     * Send subscription release success email
     *
     * @param CustomerInterface $customer
     * @param SubscriptionInterface $subscription
     * @return array
     */
    public function success(
        CustomerInterface $customer,
        SubscriptionInterface $subscription
    ): array {
        $subscriptionItems = $this->subscriptionEmail->getSubscriptionItems(
            $subscription->getId()
        );
        $data = [
            'customer_name' => sprintf(
                '%1$s %2$s',
                $customer->getFirstname(),
                $customer->getLastname()
            ),
            'subscription' => $subscription,
            'frequency_label' => $this->subscriptionEmail->getSubscribedFrequencyLabel(
                $subscriptionItems,
                $subscription->getFrequency()
            ),
            'items' => $subscriptionItems,
            'formatted_next_release' => $this->formatDate(
                $subscription->getNextReleaseDate()
            )
        ];
        $template = $this->getScopeConfig()->getValue(
            self::CONFIG_REMINDER,
            ScopeInterface::SCOPE_STORE
        ) ?? self::TEMPLATE_REMINDER;
        $this->eventManager->dispatch(
            'send_reminder_email_before',
            [
                'data' => $data,
                'template' => $template
            ]
        );
        return $this->sendEmail(
            $data,
            $customer,
            $template
        );
    }

    /**
     * Send price changed email
     *
     * @param CustomerInterface $customer
     * @param SubscriptionInterface $subscription
     * @param OrderInterface $originalOrder
     * @param OrderInterface $newOrder
     * @return array
     */
    public function priceChanged(
        CustomerInterface $customer,
        SubscriptionInterface $subscription,
        OrderInterface $originalOrder,
        OrderInterface $newOrder
    ): array {
        $subscriptionItems = $this->subscriptionEmail->getSubscriptionItems(
            $subscription->getId()
        );
        $data = [
            'customer_name' => sprintf(
                '%1$s %2$s',
                $customer->getFirstname(),
                $customer->getLastname()
            ),
            'subscription' => $subscription,
            'items' => $subscriptionItems,
            'original_order_total' => $this->pricingHelper->currency($originalOrder->getGrandTotal(), true, false),
            'new_order_total' => $this->pricingHelper->currency($newOrder->getGrandTotal(), true, false)
        ];
        return $this->sendEmail(
            $data,
            $customer,
            $this->getScopeConfig()->getValue(
                self::CONFIG_PRICE_CHANGED,
                ScopeInterface::SCOPE_STORE
            ) ?? self::TEMPLATE_PRICE_CHANGED
        );
    }

    /**
     * Send subscription release failure email
     *
     * @param CustomerInterface $customer
     * @param SubscriptionInterface $subscription
     * @param string $reason
     * @return array
     */
    public function failure(
        CustomerInterface $customer,
        SubscriptionInterface $subscription,
        string $reason
    ): array {
        $data = [
            'customer_name' => sprintf(
                '%1$s %2$s',
                $customer->getFirstname(),
                $customer->getLastname()
            ),
            'failure_reason' => $reason,
            'subscription' => $subscription,
            'items' => $this->subscriptionEmail->getSubscriptionItems(
                $subscription->getId()
            ),
            'maskedCC' => $this->paymentDetails->getMaskedCardNumber(
                $subscription
            ) ?: __('Not visible'),
            'expirationDate' => $this->paymentDetails->getCardExpiry(
                $subscription
            ) ?: __('Not visible'),
            'formatted_next_release' => $this->formatDate(
                $subscription->getNextReleaseDate()
            )
        ];
        return $this->sendEmail(
            $data,
            $customer,
            $this->getCustomTemplate()
        );
    }

    /**
     * Send subscription release failure email to admin
     *
     * @param string $reason
     * @param SubscriptionInterface $subscription
     * @return array
     */
    public function failureAdmin(
        string $reason,
        SubscriptionInterface $subscription
    ): array {
        $data = [
            'customer_name' => 'Admin',
            'failure_reason' => $reason,
            'order_items' => [],
            'formatted_next_release' => $this->formatDate(
                $subscription->getNextReleaseDate()
            ),
            'subscription' => $subscription
        ];
        return $this->sendEmailAdmin(
            $data,
            $this->getCustomAdminTemplate()
        );
    }

    /**
     * Get custom email template
     *
     * @return string
     */
    private function getCustomTemplate(): string
    {
        $template = $this->getScopeConfig()->getValue(
            self::CONFIG_FAILURE,
            ScopeInterface::SCOPE_STORE
        );
        return $template ?? self::TEMPLATE_FAILURE;
    }

    /**
     * Get custom admin email template
     *
     * @return string
     */
    private function getCustomAdminTemplate(): string
    {
        $template = $this->getScopeConfig()->getValue(
            self::CONFIG_FAILURE_ADMIN,
            ScopeInterface::SCOPE_STORE
        );
        return $template ?? self::TEMPLATE_FAILURE_ADMIN;
    }
}
