<?php

declare(strict_types=1);

namespace PayPal\Subscription\Api;

/**
 * @api
 */
interface BraintreeInterface
{
    /**
     * @return \PayPal\Subscription\Api\Data\BraintreeDataInterface
     */
    public function getClientToken(): \PayPal\Subscription\Api\Data\BraintreeDataInterface;
}
