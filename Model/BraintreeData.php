<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Magento\Framework\Model\AbstractModel;
use PayPal\Subscription\Api\Data\BraintreeDataInterface;

class BraintreeData extends AbstractModel implements BraintreeDataInterface
{
    /**
     * @inheritDoc
     */
    public function getToken()
    {
        return $this->getData('token');
    }

    /**
     * @inheritDoc
     */
    public function setToken($token)
    {
        return $this->setData('token', $token);
    }

    /**
     * @inheritDoc
     */
    public function getError()
    {
        return $this->getData('error');
    }

    /**
     * @inheritDoc
     */
    public function setError($error)
    {
        return $this->setData('error', $error);
    }
}
