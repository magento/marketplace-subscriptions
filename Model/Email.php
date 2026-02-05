<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\Template\TransportBuilderFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Email
{
    /**
     * @var TransportBuilderFactory
     */
    private $transportBuilderFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConfigurationInterface
     */
    protected $configuration;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * Email constructor.
     *
     * @param TransportBuilderFactory $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param ManagerInterface $eventManager
     * @param LoggerInterface $logger
     * @param ConfigurationInterface $configuration
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        TransportBuilderFactory $transportBuilder,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ManagerInterface $eventManager,
        LoggerInterface $logger,
        ConfigurationInterface $configuration,
        TimezoneInterface $timezone
    ) {
        $this->transportBuilderFactory = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->configuration = $configuration;
        $this->timezone = $timezone;
    }

    /**
     * Get scope config
     *
     * @return ScopeConfigInterface
     */
    public function getScopeConfig(): ScopeConfigInterface
    {
        return $this->scopeConfig;
    }

    /**
     * Send email
     *
     * @param array $data
     * @param CustomerInterface $customer
     * @param string $template
     * @return array
     */
    public function sendEmail(
        array $data,
        CustomerInterface $customer,
        $template
    ): array {
        try {
            $transportBuilder = $this->buildTransport(
                $data,
                $template
            );
            $transportBuilder->addTo(
                $customer->getEmail(),
                $customer->getFirstname()
            );
            $transportBuilder->getTransport()->sendMessage();
            return [
                'data' => $data,
                'customer' => $customer,
                'template' => $template
            ];
        } catch (NoSuchEntityException | MailException | LocalizedException $exception) {
            $this->logger->debug($exception);
            return [];
        }
    }

    /**
     * Send admin email
     *
     * @param array $data
     * @param string $template
     * @return array
     */
    public function sendEmailAdmin(array $data, $template): array
    {
        try {
            $transportBuilder = $this->buildTransport($data, $template);
            $adminErrorEmailRecipients = $this->configuration->getErrorLoggingEmailsRecipients() ?: '';
            $adminErrorEmailRecipients = explode(
                ',',
                $adminErrorEmailRecipients
            ) ?? [];
            if ($adminErrorEmailRecipients !== []) {
                foreach ($adminErrorEmailRecipients as $adminErrorRecipient) {
                    $transportBuilder->addTo(
                        trim($adminErrorRecipient),
                        $this->getScopeConfig()->getValue('general/store_information/name')
                    );
                }
                $transportBuilder->getTransport()->sendMessage();
            }
            return [
                'data' => $data,
                'template' => $template
            ];
        } catch (NoSuchEntityException | MailException | LocalizedException $exception) {
            return [];
        }
    }

    /**
     * Send emails to multiple recipients
     *
     * @param array $data
     * @param array $emailAddresses
     * @param CustomerInterface $customer
     * @param string $template
     * @return array
     */
    public function sendMultipleRecipientsEmail(
        array $data,
        array $emailAddresses,
        CustomerInterface $customer,
        string $template
    ): array {
        try {
            $transportBuilder = $this->buildTransport(
                $data,
                $template
            );
            foreach ($emailAddresses as $emailAddress) {
                $transportBuilder->addTo($emailAddress);
            }
            $transportBuilder->getTransport()->sendMessage();
            return [
                'data' => $data,
                'customer' => $customer,
                'template' => $template
            ];
        } catch (NoSuchEntityException | MailException | LocalizedException $exception) {
            $this->logger->debug($exception);
            return [];
        }
    }

    /**
     * Build transport
     *
     * @param array $data
     * @param string $template
     * @return TransportBuilder
     * @throws MailException
     * @throws NoSuchEntityException
     */
    private function buildTransport(array $data, $template): TransportBuilder
    {
        /** @var TransportBuilder $transportBuilder */
        $transportBuilder = $this->transportBuilderFactory->create();
        $transportBuilder->setTemplateIdentifier($template);
        $transportBuilder->setTemplateOptions(
            [
                'area' => Area::AREA_FRONTEND,
                'store' => $this->storeManager->getStore()->getId()
            ]
        );
        $transportBuilder->setTemplateVars($data);
        $transportBuilder->setFromByScope(
            [
                'name' => $this->getScopeConfig()->getValue('general/store_information/name') ??
                    $this->getScopeConfig()->getValue('trans_email/ident_general/name'),
                'email' => $this->getScopeConfig()->getValue('trans_email/ident_general/email')
            ],
            $this->storeManager->getStore()->getId()
        );
        return $transportBuilder;
    }

    /**
     * Return Formatted date string
     *
     * @param string $date
     * @return string
     */
    public function formatDate(
        string $date
    ): string {
        return $this->timezone->formatDate($date);
    }
}
