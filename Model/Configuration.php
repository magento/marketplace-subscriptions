<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PayPal\Subscription\Model\Config\Source\Subscription\MessageBroker;

class Configuration implements ConfigurationInterface
{
    /**
     * Configuration constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getActive(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = null
    ): bool {
        return (bool) $this->scopeConfig->getValue(
            self::ACTIVE_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * @inheritDoc
     */
    public function getMessageBroker(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = null
    ): int {
        $messageBrokerConfigValue = $this->scopeConfig->getValue(
            self::MESSAGE_BROKER_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
        return $messageBrokerConfigValue === null ?
            MessageBroker::MAGENTO_DATABASE_BROKER_CONFIG_VALUE :
            (int) $messageBrokerConfigValue;
    }

    /**
     * @inheritDoc
     */
    public function getStockFailuresAllowed(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = null
    ): ?int {
        $stockFailuresAllowed = $this->scopeConfig->getValue(
            self::STOCK_FAILURES_ALLOWED_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
        return $stockFailuresAllowed === null ?
            $stockFailuresAllowed :
            (int) $stockFailuresAllowed;
    }

    /**
     * @inheritDoc
     */
    public function getFailedPaymentsAllowed(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = null
    ): ?int {
        $failedPaymentsAllowed = $this->scopeConfig->getValue(
            self::FAILED_PAYMENTS_ALLOWED_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
        return $failedPaymentsAllowed === null ?
            $failedPaymentsAllowed :
            (int) $failedPaymentsAllowed;
    }

    /**
     * @inheritDoc
     */
    public function getReleaseShippingMethod(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = null
    ): ?string {
        $releaseShippingMethod = $this->scopeConfig->getValue(
            self::RELEASE_SHIPPING_METHOD_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
        return $releaseShippingMethod === null ?
            $releaseShippingMethod :
            (string) $releaseShippingMethod;
    }

    /**
     * Get allowed payment methods
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return string|null
     */
    public function getAllowedPaymentMethods(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = null
    ): ?string {
        $allowedPaymentMethods = $this->scopeConfig->getValue(
            self::ALLOWED_PAYMENT_METHODS_CONFIG_PATH,
            $scopeType,
            $scopeCode
        );
        return $allowedPaymentMethods === null ?
            $allowedPaymentMethods :
            (string) $allowedPaymentMethods;
    }

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
    ): bool {
        return (bool) $this->scopeConfig->getValue(
            self::ERROR_LOGGING_EMAIL_ENABLED,
            $scopeType,
            $scopeCode
        );
    }

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
    ): ?string {
        $errorLoggingRecipients = $this->scopeConfig->getValue(
            self::ERROR_LOGGING_EMAIL_RECIPIENTS,
            $scopeType,
            $scopeCode
        );
        return $errorLoggingRecipients === null ?
            $errorLoggingRecipients :
            (string) $errorLoggingRecipients;
    }

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
    ): int {
        return (int) $this->scopeConfig->getValue(
            self::RELEASE_REMINDER_TIMING,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * @inheritDoc
     */
    public function getAutoUpdatePrice(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = null
    ): bool {
        return (bool) $this->scopeConfig->getValue(
            self::AUTO_UPDATE_PRICE,
            $scopeType,
            $scopeCode
        );
    }
}
