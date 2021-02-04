<?php

declare(strict_types=1);

namespace PayPal\Subscription\Block\Adminhtml\Subscriptions\Edit;

use Magento\Backend\Block\Template;

class Payment extends Template
{
    public function getFormAction()
    {
        return $this->getUrl(
            'paypal_subscription/subscriptions/addpaymentmethod',
            ['id' => $this->getRequest()->getParam('id')]
        );
    }
}
