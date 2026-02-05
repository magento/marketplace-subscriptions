<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Magento\Framework\Model\AbstractModel;
use PayPal\Subscription\Api\Data\BraintreeDataInterface;

class BraintreeData extends AbstractModel implements BraintreeDataInterface
{
    /**
     * Get token
     *
     * @return string
     */
    public function getToken(): string
    {
        return $this->getData('token');
    }

    /**
     * Set token
     *
     * @param string $token
     * @return BraintreeDataInterface
     */
    public function setToken($token): BraintreeDataInterface
    {
        return $this->setData('token', $token);
    }

    /**
     * Get error
     *
     * @return string
     */
    public function getError(): string
    {
        return $this->getData('error');
    }

    /**
     * Set error
     *
     * @param string $error
     * @return BraintreeDataInterface
     */
    public function setError($error): BraintreeDataInterface
    {
        return $this->setData('error', $error);
    }
}
