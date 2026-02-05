<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Controller;

use Magento\Customer\Model\Session;
use PayPal\Subscription\Api\Data\SubscriptionInterface;

class SubscriptionViewAuthorisation
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * SubscriptionViewAuthorisation constructor.
     *
     * @param Session $customerSession
     */
    public function __construct(Session $customerSession)
    {
        $this->customerSession = $customerSession;
    }

    /**
     * Can view subscriptions
     *
     * @param SubscriptionInterface $subscription
     * @return bool
     */
    public function canView(SubscriptionInterface $subscription): bool
    {
        $customerId = (int) $this->customerSession->getCustomerId();
        return $subscription->getCustomerId() === $customerId;
    }
}
