<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\Template\TransportBuilderFactory;
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
     * Email constructor.
     *
     * @param TransportBuilderFactory $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        TransportBuilderFactory $transportBuilder,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->transportBuilderFactory = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * @return ScopeConfigInterface
     */
    public function getScopeConfig()
    {
        return $this->scopeConfig;
    }

    /**
     * @param array $data
     * @param CustomerInterface $customer
     * @param $template
     * @return array
     */
    public function sendEmail(array $data, CustomerInterface $customer, $template)
    {
        try {
            $transportBuilder = $this->buildTransport($data, $template);
            $transportBuilder->addTo($customer->getEmail(), $customer->getFirstname());
            $transportBuilder->getTransport()->sendMessage();
            return ['data' => $data, 'customer' => $customer, 'template' => $template];
        } catch (NoSuchEntityException | MailException | LocalizedException $exception) {
            $this->logger->debug($exception);
            return [];
        }
    }

    /**
     * @param array $data
     * @param $template
     * @return array
     */
    public function sendEmailAdmin(array $data, $template)
    {
        try {
            $transportBuilder = $this->buildTransport($data, $template);
            $transportBuilder->addTo(
                $this->getScopeConfig()->getValue('trans_email/ident_general/email'),
                $this->getScopeConfig()->getValue('general/store_information/name')
            );
            $transportBuilder->getTransport()->sendMessage();
            return ['data' => $data, 'template' => $template];
        } catch (NoSuchEntityException | MailException | LocalizedException $exception) {
            return [];
        }
    }

    /**
     * @param array $data
     * @param $template
     * @return TransportBuilder
     * @throws MailException
     * @throws NoSuchEntityException
     */
    private function buildTransport(array $data, $template)
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
}
