<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Controller\Customer;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use PayPal\Subscription\Controller\AbstractSubscriptionController;

class History extends AbstractSubscriptionController implements HttpGetActionInterface, HttpPostActionInterface
{
    /** @var string */
    protected $title = 'View History';
}
