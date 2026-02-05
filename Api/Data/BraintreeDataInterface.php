<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Api\Data;

/**
 * @api
 */
interface BraintreeDataInterface
{
    /**
     * Get braintree token
     *
     * @return string
     */
    public function getToken(): string;

    /**
     * Set braintree token
     *
     * @param string $token
     * @return $this
     */
    public function setToken(string $token): self;

    /**
     * Get braintree error
     *
     * @return string
     */
    public function getError(): string;

    /**
     * Set braintree error
     *
     * @param string $error
     * @return $this
     */
    public function setError(string $error): self;
}
