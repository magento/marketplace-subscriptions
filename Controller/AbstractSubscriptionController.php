<?php

declare(strict_types=1);

namespace PayPal\Subscription\Controller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;

abstract class AbstractSubscriptionController extends Action
{
    /**
     * @var string
     */
    protected $title = 'Subscriptions';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var SubscriptionLoader
     */
    private $subscriptionLoader;

    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * AbstractSubscriptionController constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param SubscriptionLoader $subscriptionLoader
     * @param SubscriptionHelper $subscriptionHelper
     * @param RedirectFactory $redirectFactory
     * @param UrlInterface $url
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        SubscriptionLoader $subscriptionLoader,
        SubscriptionHelper $subscriptionHelper,
        RedirectFactory $redirectFactory,
        UrlInterface $url
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->subscriptionLoader = $subscriptionLoader;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->redirectFactory = $redirectFactory;
        $this->url = $url;
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     * @throws LocalizedException
     */
    public function execute()
    {
        if (!$this->subscriptionHelper->isActive()) {
            $resultRedirect = $this->redirectFactory->create();
            return $resultRedirect->setUrl($this->url->getUrl('customer/account/index'));
        }

        if ($this->_request->getParam('id')) {
            $result = $this->subscriptionLoader->load($this->_request);
            if ($result instanceof ResultInterface) {
                return $result;
            }
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__($this->title));

        return $resultPage;
    }
}
