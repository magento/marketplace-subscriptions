<?php
declare(strict_types=1);

namespace PayPal\Subscription\ViewModel\Customer;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
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
     * Get Subscription ID
     *
     * @return int
     */
    public function getSubscriptionId(): int
    {
        return (int) $this->request->getParam('id');
    }

    /**
     * Get validation class
     *
     * @param string $value
     * @return string
     * @throws LocalizedException
     */
    public function getValidationClass($value): string
    {
        return $this->addressHelper->getAttributeValidationClass($value);
    }

    /**
     * Get customer address
     *
     * @return AddressHelper
     */
    public function getCustomerAddress(): AddressHelper
    {
        return $this->addressHelper;
    }

    /**
     * Get directory
     *
     * @return DirectoryHelper
     */
    public function getDirectory(): DirectoryHelper
    {
        return $this->directoryHelper;
    }
}
