<?php

declare(strict_types=1);

namespace PayPal\Subscription\Controller\Adminhtml\Subscriptions;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use PayPal\Subscription\Api\BraintreePaymentInterface;
use PayPal\Subscription\Api\SubscriptionRepositoryInterface;

/**
 * Controller for editing Frequency Profiles
 */
class AddPaymentMethod extends Action
{
    public const ADMIN_RESOURCE = 'PayPal_Subscription::subscriptions_edit';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var BraintreePaymentInterface
     */
    protected $braintreePayment;

    /**
     * @var SubscriptionRepositoryInterface
     */
    protected $subscriptionRepository;

    /**
     * AddPaymentMethod constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     * @param BraintreePaymentInterface $braintreePayment
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        SubscriptionRepositoryInterface $subscriptionRepository,
        BraintreePaymentInterface $braintreePayment
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->braintreePayment = $braintreePayment;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $id = (int) $this->getRequest()->getParam('id');
        try {
            $subscription = $this->subscriptionRepository->getById($id);

            $this->braintreePayment->changePaymentMethodNew(
                $subscription->getCustomerId(),
                $subscription->getId(),
                $this->getRequest()->getParam('payment_method_nonce'),
                \Magento\Braintree\Model\Ui\ConfigProvider::CODE
            );
            return $this->_redirect('*/*/edit', ['id' => $id]);
        } catch (LocalizedException $e) {
            // do nothing as the service class has already set a session message
            return $this->_redirect('*/*/edit', ['id' => $id]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Could not save new payment method: %1', $e->getMessage()));
            return $this->_redirect('*/*/edit', ['id' => $id]);
        }
    }

    /**
     * @return bool
     */
    public function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
