<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Magento\Store\Model\ScopeInterface;

interface ConfigurationInterface
{
    public const ACTIVE_CONFIG_PATH = 'paypal/subscriptions/active';
    public const MESSAGE_BROKER_CONFIG_PATH = 'paypal/subscriptions/message_broker';
    public const STOCK_FAILURES_ALLOWED_CONFIG_PATH = 'paypal/subscriptions/stock_failures_allowed';
    public const FAILED_PAYMENTS_ALLOWED_CONFIG_PATH = 'paypal/subscriptions/failed_payments';
    public const RELEASE_SHIPPING_METHOD_CONFIG_PATH = 'paypal/subscriptions/release_shipping_method';
    public const ALLOWED_PAYMENT_METHODS_CONFIG_PATH = 'paypal/subscriptions/allowed_payment_methods';
    public const ERROR_LOGGING_EMAIL_ENABLED = 'paypal_subscriptions/email_configuration/error_logging_emails_enabled';
    public const
        ERROR_LOGGING_EMAIL_RECIPIENTS ='paypal_subscriptions/email_configuration/error_logging_emails_recipients';
    public const RELEASE_REMINDER_TIMING = 'paypal_subscriptions/email_configuration/release_reminder_timing';
    public const AUTO_UPDATE_PRICE = 'paypal/subscriptions/auto_update_price';

    /**
     * Get Configuration value for Is Active
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return bool
     */
    public function getActive(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = null
    ): bool;

    /**
     * Get Configuration value for Message Broker
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return int
     */
    public function getMessageBroker(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = null
    ): int;

    /**
     * Get Configuration value for how many stock failures are allowed before subscription is cancelled
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return int|null
     */
    public function getStockFailuresAllowed(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = null
    ): ?int;

    /**
     * Get Configuration value for how many failed payments are allowed before subscription is cancelled
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return int|null
     */
    public function getFailedPaymentsAllowed(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = null
    ): ?int;

    /**
     * Get Configuration value for Subscription Release Shipping Method
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return string|null
     */
    public function getReleaseShippingMethod(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = null
    ): ?string;

    /**
     * Get Configuration value for whether Error Logging emails are enabled
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return bool
     */
    public function getErrorLoggingEmailsEnabled(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = null
    ): bool;

    /**
     * Get Configuration value for Error Logging emails recipients
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return string|null
     */
    public function getErrorLoggingEmailsRecipients(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = null
    ): ?string;

    /**
     * Get Configuration value for Release reminder email timing
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return int
     */
    public function getReleaseReminderEmailTiming(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = null
    ): int;

    /**
     * Get Configuration value for auto update product prices.
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return bool
     */
    public function getAutoUpdatePrice(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = null
    ): bool;
}
