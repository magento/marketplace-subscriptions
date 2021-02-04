<?php

declare(strict_types=1);

namespace PayPal\Subscription\Test\Integration;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Sales\Model\Order;
use Magento\TestFramework\ObjectManager;
use PayPal\Subscription\Model\FrequencyProfile;
use PayPal\Subscription\Model\ResourceModel\FrequencyProfile as FrequencyResource;
use PayPal\Subscription\Model\ResourceModel\Subscription as SubscriptionResource;
use PayPal\Subscription\Model\ResourceModel\Subscription\Collection;
use PayPal\Subscription\Model\Subscription;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PayPal\Subscription\Model\Subscription
 * @covers \PayPal\Subscription\Model\ResourceModel\Subscription
 * @magentoDbIsolation enabled
 */
class SubscriptionOrmEntityTest extends TestCase
{
    /**
     * @return Subscription
     */
    public function instantiateFrequencyProfile(): Subscription
    {
        return ObjectManager::getInstance()->create(Subscription::class);
    }

    /**
     * @return SubscriptionResource
     */
    public function instantiateResourceModel(): SubscriptionResource
    {
        return ObjectManager::getInstance()->create(SubscriptionResource::class);
    }

    /**
     * Custom data fixture to add a frequency profile
     * @return void
     */
    public static function frequencyProfileDataFixture(): void
    {
        $frequencyProfile = ObjectManager::getInstance()->create(FrequencyProfile::class);
        $frequencyProfile->setName(uniqid('Profile-', true));
        $frequencyProfile->setFrequencyOptions(json_encode(['foo' => 'bar']));
        ObjectManager::getInstance()->create(FrequencyResource::class)->save($frequencyProfile);
    }

    /**
     * @param FrequencyProfile $frequencyProfile
     * @param Order $order
     * @return Subscription
     * @throws AlreadyExistsException
     */
    public function createSubscription(FrequencyProfile $frequencyProfile, Order $order): Subscription
    {
        $subscription = $this->instantiateFrequencyProfile();
        $subscription->setCustomerId(1);
        $subscription->setFrequencyProfileId((int)$frequencyProfile->getId());
        $subscription->setOrderId((int)$order->getId());
        $this->instantiateResourceModel()->save($subscription);
        return $subscription;
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture frequencyProfileDataFixture
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @throws AlreadyExistsException
     */
    public function testCanSaveAndLoad(): void
    {
        /** @var FrequencyProfile $frequencyProfile */
        $frequencyProfile = ObjectManager::getInstance()
            ->create(FrequencyProfile::class)
            ->getCollection()
            ->getFirstItem();

        /** @var Order $order */
        $order = ObjectManager::getInstance()->create(Order::class);
        $order->loadByIncrementId('100000001');

        $subscription = $this->createSubscription($frequencyProfile, $order);

        $subscriptionToLoad = $this->instantiateFrequencyProfile();
        $this->instantiateResourceModel()->load($subscriptionToLoad, $subscription->getId());

        $this->assertSame($subscription->getId(), $subscriptionToLoad->getId());
        $this->assertSame($subscription->getCustomerId(), $subscriptionToLoad->getCustomerId());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture frequencyProfileDataFixture
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @throws AlreadyExistsException
     */
    public function testCanLoadMultipleSubscriptions(): void
    {
        /** @var FrequencyProfile $frequencyProfile */
        $frequencyProfile = ObjectManager::getInstance()
            ->create(FrequencyProfile::class)
            ->getCollection()
            ->getFirstItem();

        /** @var Order $order */
        $order = ObjectManager::getInstance()->create(Order::class);
        $order->loadByIncrementId('100000001');

        $subscriptionA = $this->createSubscription($frequencyProfile, $order);
        $subscriptionB = $this->createSubscription($frequencyProfile, $order);

        /** @var Collection $collection */
        $collection = ObjectManager::getInstance()->create(Collection::class);
        $this->assertContains($subscriptionA->getId(), array_keys($collection->getItems()));
        $this->assertContains($subscriptionB->getId(), array_keys($collection->getItems()));
    }
}
