<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;
use PayPal\Subscription\Api\Data\SubscriptionItemInterface;
use PayPal\Subscription\Api\SubscriptionItemRepositoryInterface;
use PayPal\Subscription\Api\UpdateSubscriptionItemQtyInterface;

class UpdateSubscriptionItemQty implements UpdateSubscriptionItemQtyInterface
{
    private const UPDATE_ITEM_QTY_BEFORE_EVENT = 'update_subscription_item_qty_before';
    private const UPDATE_ITEM_QTY_AFTER_EVENT = 'update_subscription_item_qty_after';

    /**
     * @var SubscriptionItemRepositoryInterface
     */
    private $subscriptionItemRepository;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * UpdateSubscriptionItemQty constructor
     *
     * @param ManagerInterface $eventManager
     * @param SubscriptionItemRepositoryInterface $subscriptionItemRepository
     */
    public function __construct(
        ManagerInterface $eventManager,
        SubscriptionItemRepositoryInterface $subscriptionItemRepository
    ) {
        $this->eventManager = $eventManager;
        $this->subscriptionItemRepository = $subscriptionItemRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute(
        int $subscriptionItemId,
        int $quantity,
        int $customerId
    ): SubscriptionItemInterface {
        /** @var SubscriptionItemInterface $subscriptionItem */
        $subscriptionItem = $this->subscriptionItemRepository->getById(
            $subscriptionItemId
        );
        $originalQty = $subscriptionItem->getQty();
        $this->eventManager->dispatch(
            self::UPDATE_ITEM_QTY_BEFORE_EVENT,
            [
                'item' => $subscriptionItem,
                'old_qty' => $originalQty,
                'new_qty' =>$quantity
            ]
        );
        $subscriptionItem->setQty($quantity);
        $this->subscriptionItemRepository->save($subscriptionItem);
        $this->eventManager->dispatch(
            self::UPDATE_ITEM_QTY_AFTER_EVENT,
            [
                'item' => $subscriptionItem,
                'old_qty' => $originalQty,
                'new_qty' => $quantity
            ]
        );

        return $subscriptionItem;
    }
}
