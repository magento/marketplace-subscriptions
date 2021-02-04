<?php

declare(strict_types=1);

namespace PayPal\Subscription\Plugin\Controller\Customer;

use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;

class Authenticate
{
    /**
     * @var Url
     */
    protected $customerUrl;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @param Url $customerUrl
     * @param Session $customerSession
     */
    public function __construct(
        Url $customerUrl,
        Session $customerSession
    ) {
        $this->customerUrl = $customerUrl;
        $this->customerSession = $customerSession;
    }

    /**
     * @param ActionInterface $subject
     * @param RequestInterface $request
     */
    public function beforeDispatch(ActionInterface $subject, RequestInterface $request)
    {
        $loginUrl = $this->customerUrl->getLoginUrl();

        if (!$this->customerSession->authenticate($loginUrl)) {
            $subject->getActionFlag()->set('', $subject::FLAG_NO_DISPATCH, true);
        }
    }
}
