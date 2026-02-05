<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PayPal\Subscription\Controller\Adminhtml\Customer;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Customer\Controller\Adminhtml\Index;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\View\Result\Layout;

class Subscriptions extends Index implements ActionInterface, HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * Customer Subscriptions grid
     *
     * @return Layout
     */
    public function execute()
    {
        $this->initCurrentCustomer();
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
