<?php

declare(strict_types=1);

namespace PayPal\Subscription\ViewModel\Customer;

use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Customer\Helper\Address as AddressHelper;
use Magento\Directory\Helper\Data as DirectoryHelper;

class Address implements ArgumentInterface
{
    /**
     * @var Http
     */
    protected $request;

    /**
     * @var AddressHelper
     */
    protected $addressHelper;

    /**
     * @var DirectoryHelper
     */
    protected $directoryHelper;

    /**
     * Address constructor.
     * @param Http $request
     * @param AddressHelper $addressHelper
     * @param DirectoryHelper $directoryHelper
     */
    public function __construct(
        Http $request,
        AddressHelper $addressHelper,
        DirectoryHelper $directoryHelper
    ) {
        $this->request = $request;
        $this->addressHelper = $addressHelper;
        $this->directoryHelper = $directoryHelper;
    }

    /**
     * Get Subscription id
     * @return int
     */
    public function getSubscriptionId(): int
    {
        return (int) $this->request->getParam('id');
    }

    /**
     * @param $value
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getValidationClass($value)
    {
        return $this->addressHelper->getAttributeValidationClass($value);
    }

    /**
     * @return AddressHelper
     */
    public function getCustomerAddress()
    {
        return $this->addressHelper;
    }

    /**
     * @return DirectoryHelper
     */
    public function getDirectory()
    {
        return $this->directoryHelper;
    }
}
