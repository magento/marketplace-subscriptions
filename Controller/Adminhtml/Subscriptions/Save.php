<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Controller\Adminhtml\Subscriptions;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use PayPal\Subscription\Api\SubscriptionRepositoryInterface;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;
use PayPal\Subscription\Model\Subscription;

/**
 * Controller for saving the subscriptions
 */
class Save extends Action implements HttpPostActionInterface
{
    /**
     * @var SubscriptionRepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var PaymentTokenManagementInterface
     */
    private $paymentTokenManagement;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     * @param SubscriptionHelper $subscriptionHelper
     * @param AddressRepositoryInterface $addressRepository
     * @param PaymentTokenManagementInterface $paymentTokenManagement
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Context $context,
        SubscriptionRepositoryInterface $subscriptionRepository,
        SubscriptionHelper $subscriptionHelper,
        AddressRepositoryInterface $addressRepository,
        PaymentTokenManagementInterface $paymentTokenManagement,
        SerializerInterface $serializer
    ) {
        parent::__construct($context);
        $this->subscriptionRepository = $subscriptionRepository;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->addressRepository = $addressRepository;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->serializer = $serializer;
    }

    /**
     * Save the subscription
     *
     * @return ResponseInterface|ResultInterface|void
     * @throws LocalizedException
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $id = (int) $data['id'];

        try {
            /** @var Subscription $subscription */
            $subscription = $this->subscriptionRepository->getById($id);
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('Could not find Subscription with ID %1', $id));
        }

        $data = array_filter($data, static function ($value) {
            return $value !== '';
        });

        if (!empty($data['shipping']['existing_billing_address'])) {
            $billingAddress = $this->addressRepository->getById($data['shipping']['existing_billing_address']);
            $subscription->setBillingAddress($this->subscriptionHelper->getSerialisedAddress($billingAddress));
        }

        if (!empty($data['shipping']['existing_shipping_address'])) {
            $shippingAddress = $this->addressRepository->getById($data['shipping']['existing_shipping_address']);
            $subscription->setShippingAddress($this->subscriptionHelper->getSerialisedAddress($shippingAddress));
        }

        if (!empty($data['shipping']['available_shipping_method'])) {
            $subscription->setShippingMethod($data['shipping']['available_shipping_method']);
            try {
                $methods = $this->subscriptionHelper->getShipping($id);
            } catch (LocalizedException $e) {
                $methods = [];
            }
            foreach ($methods ?? [] as $method) {
                if ($method->getCode() == $subscription->getShippingMethod()) {
                    $subscription->setShippingPrice($method->getPrice());
                    break;
                }
            }
        }

        if (!empty($data['payment']['existing_payment_method'])) {
            $paymentMethod = $this->paymentTokenManagement->getByPublicHash(
                $data['payment']['existing_payment_method'],
                $subscription->getCustomerId()
            );
            $subscription->setPaymentMethod($paymentMethod->getPaymentMethodCode());
            $subscription->setPaymentData(
                $this->serializer->serialize(
                    [
                        'gateway_token' => $paymentMethod->getGatewayToken(),
                        'public_hash' => $paymentMethod->getPublicHash()
                    ]
                )
            );
        }

        $subscription->setFrequency((int) $data['overview']['frequency']);
        $subscription->setStatus((int) $data['overview']['status']);
        if ($subscription->getId() && $subscription->getOrigData('frequency') != $subscription->getData('frequency')) {
            $frequency = (int) $subscription->getData('frequency');
            $oldFrequency = (int) $subscription->getOrigData('frequency');
            $difference = $frequency - $oldFrequency;
            $nextRelease = $subscription->getNextReleaseDate();
            $date = new \DateTime($nextRelease);
            $date->modify($difference . "days");
            $today = new \DateTime();
            if ($date < $today) {
                $date = $today->modify('1 day');
            }
            $subscription->setNextReleaseDate($date->format("Y-m-d H:i:s"));
        }

        try {
            $this->subscriptionRepository->save($subscription);
            $changes = array_diff_assoc($subscription->getData(), $subscription->getOrigData());
            $subscription->addHistory(
                'Subscription Changes',
                'admin',
                sprintf(
                    'The store admin changed the following: %s',
                    implode(', ', array_keys($changes))
                ),
                (bool) $data['overview']['visible_to_customer'],
                (bool) $data['overview']['notify_customer']
            );
            $this->messageManager->addSuccessMessage(
                __('Subscription "%1" has been saved successfully', $data['id'])
            );
        } catch (AlreadyExistsException $e) {
            throw new LocalizedException(__('Could not save Subscription'));
        }

        if ($this->getRequest()->getParam('back')) {
            $this->_redirect('*/*/edit', ['id' => $data['id'], '_current' => true]);
        } else {
            $this->_redirect('*/*/index');
        }
    }
}
