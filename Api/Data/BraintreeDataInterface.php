<?php

declare(strict_types=1);

namespace PayPal\Subscription\Api\Data;

/**
 * @api
 */
interface BraintreeDataInterface
{
    /**
     * @return string
     */
    public function getToken();

    /**
     * @param $token
     * @return $this
     */
    public function setToken($token);

    /**
     * @return string
     */
    public function getError();

    /**
     * @param $error
     * @return $this
     */
    public function setError($error);
}
