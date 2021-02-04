<?php

declare(strict_types=1);

namespace PayPal\Subscription\Controller;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use PayPal\Subscription\Api\SubscriptionRepositoryInterface;

class SubscriptionLoader
{
    /**
     * @var SubscriptionRepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var SubscriptionViewAuthorisation
     */
    private $subscriptionViewAuthorisation;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var UrlInterface
     */
    private $url;

    public function __construct(
        SubscriptionRepositoryInterface $subscriptionRepository,
        SubscriptionViewAuthorisation $subscriptionViewAuthorisation,
        RedirectFactory $redirectFactory,
        UrlInterface $url
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->subscriptionViewAuthorisation = $subscriptionViewAuthorisation;
        $this->redirectFactory = $redirectFactory;
        $this->url = $url;
    }

    /**
     * @param RequestInterface $request
     * @return bool|Redirect
     * @throws LocalizedException
     */
    public function load(RequestInterface $request)
    {
        try {
            $subscriptionId = $request->getParam('id');
            $subscription = $this->subscriptionRepository->getById((int)$subscriptionId);
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('Cannot find subscription.'));
        }

        if ($this->subscriptionViewAuthorisation->canView($subscription)) {
            return true;
        }

        $resultRedirect = $this->redirectFactory->create();
        return $resultRedirect->setUrl($this->url->getUrl('*/*/index'));
    }
}
