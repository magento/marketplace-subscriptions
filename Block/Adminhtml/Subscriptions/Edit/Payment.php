<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Block\Adminhtml\Subscriptions\Edit;

use Magento\Backend\Block\Template;

class Payment extends Template
{
    /**
     * Get form action
     *
     * @return string
     */
    public function getFormAction(): string
    {
        return $this->getUrl(
            'paypal_subscription/subscriptions/addpaymentmethod',
            ['id' => $this->getRequest()->getParam('id')]
        );
    }
}
