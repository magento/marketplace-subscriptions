<?php
declare(strict_types=1);

namespace PayPal\Subscription\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use PayPal\Subscription\Api\Data\SubscriptionHistoryInterface;
use PayPal\Subscription\Api\Data\SubscriptionInterface;
use PayPal\Subscription\Api\Data\SubscriptionItemInterface;
use PayPal\Subscription\Api\SubscriptionManagementInterface;
use PayPal\Subscription\Api\SubscriptionRepositoryInterface;
use PayPal\Subscription\Model\SubscriptionItem;

class SubscriptionItemQtyChange implements ObserverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SubscriptionRepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * SubscriptionItemQtyChange constructor
     *
     * @param ProductRepositoryInterface $productRepository
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SubscriptionRepositoryInterface $subscriptionRepository
    ) {
        $this->productRepository = $productRepository;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * Add History to Subscription when Item QTY changed
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var SubscriptionItemInterface|SubscriptionItem $subscriptionItem */
        $subscriptionItem = $observer->getEvent()->getData('item') ?: null;
        if ($subscriptionItem !== null) {
            $subscriptionId = $subscriptionItem->getSubscriptionId();
            try {
                /** @var SubscriptionInterface $subscription */
                $subscription = $this->subscriptionRepository->getById(
                    (int) $subscriptionId
                );
                /** @var ProductInterface $product */
                $product = $this->productRepository->getById(
                    (int) $subscriptionItem->getProductId()
                );
                $oldQty = $observer->getEvent()->getData('old_qty');
                $newQty = $observer->getEvent()->getData('new_qty');
                $subscription->addHistory(
                    SubscriptionHistoryInterface::CHANGE_ITEM_QTY_ACTION,
                    'customer',
                    sprintf(
                        'Subscription Item "%s" quantity has been updated from %s to %s',
                        $product->getName(),
                        $oldQty,
                        $newQty
                    )
                );
            } catch (\Exception $e) {
                return;
            }
        }
    }
}
