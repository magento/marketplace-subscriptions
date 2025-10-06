<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Api;

/**
 * @api
 */
interface BraintreeInterface
{
    /**
     * Get braintree client token
     *
     * @return \PayPal\Subscription\Api\Data\BraintreeDataInterface
     */
    public function getClientToken(): \PayPal\Subscription\Api\Data\BraintreeDataInterface;
}
