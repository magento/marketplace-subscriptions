<?php

declare(strict_types=1);

namespace PayPal\Subscription\Test\Integration;

use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Model\Order;
use Magento\TestFramework\ObjectManager;
use PayPal\Subscription\Model\FrequencyProfile;
use PayPal\Subscription\Model\ResourceModel\FrequencyProfile as FrequencyResource;
use PayPal\Subscription\Model\Subscription;
use PayPal\Subscription\Model\SubscriptionManagement;
use PHPUnit\Framework\TestCase;
use TddWizard\Fixtures\Catalog\ProductBuilder;
use TddWizard\Fixtures\Catalog\ProductFixture;
use TddWizard\Fixtures\Checkout\CartBuilder;
use TddWizard\Fixtures\Checkout\CustomerCheckout;
use TddWizard\Fixtures\Customer\AddressBuilder;
use TddWizard\Fixtures\Customer\CustomerBuilder;
use TddWizard\Fixtures\Customer\CustomerFixture;

/**
 * @covers \PayPal\Subscription\Model\SubscriptionManagement
 * @magentoDbIsolation enabled
 */
class SubscriptionManagementTest extends TestCase
{
    /**
     * @var ProductFixture
     */
    private $productFixture;

    /**
     * @var CustomerFixture
     */
    private $customerFixture;

    /**
     * @throws Exception
     */
    public function setUp()
    {
        $this->productFixture = new ProductFixture(
            ProductBuilder::aSimpleProduct()->build()
        );
        $this->customerFixture = new CustomerFixture(
            CustomerBuilder::aCustomer()
                ->withAddresses(AddressBuilder::anAddress()->asDefaultBilling()->asDefaultShipping())
                ->build()
        );
    }
    /**
     * Data fixture to add frequency profile.
     */
    public static function frequencyProfileDataFixture(): void
    {
        $frequencyProfile = ObjectManager::getInstance()->create(FrequencyProfile::class);
        $frequencyProfile->setName(uniqid('Profile-', true));
        $frequencyProfile->setFrequencyOptions(json_encode(['foo' => 'bar']));
        ObjectManager::getInstance()->create(FrequencyResource::class)->save($frequencyProfile);
    }

    /**
     * @magentoDataFixture frequencyProfileDataFixture
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testCreateSubscriptionTypeErrorException()
    {
        $this->expectException(\TypeError::class);

        $order = ObjectManager::getInstance()->create(Order::class);
        $order->loadByIncrementId('100000001');

        /** @var SubscriptionManagement $subscriptionManager */
        $subscriptionManager = ObjectManager::getInstance()->create(SubscriptionManagement::class);
        $subscriptionManager->createSubscription($order, "1");
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testCreateSubscriptionCouldNotSaveException()
    {
        $this->expectException(CouldNotSaveException::class);

        $order = ObjectManager::getInstance()->create(Order::class);
        $order->loadByIncrementId('100000001');

        /** @var SubscriptionManagement $subscriptionManager */
        $subscriptionManager = ObjectManager::getInstance()->create(SubscriptionManagement::class);
        $subscriptionManager->createSubscription($order, 1);
    }

    public function testCreateSubscription()
    {
        $this->customerFixture->login();
        $checkout = CustomerCheckout::fromCart(
            CartBuilder::forCurrentSession()->withSimpleProduct($this->productFixture->getSku())->build()
        );
        $order = $checkout ->withShippingMethodCode('flatrate_flatrate')
            ->withPaymentMethodCode('checkmo')
            ->placeOrder();

        /** @var FrequencyProfile $frequencyProfile */
        $frequencyProfile = ObjectManager::getInstance()
            ->create(FrequencyProfile::class)
            ->getCollection()
            ->getFirstItem();

        /** @var SubscriptionManagement $subscriptionManager */
        $subscriptionManager = ObjectManager::getInstance()->create(SubscriptionManagement::class);
        $subscription = $subscriptionManager->createSubscription($order, 1, $frequencyProfile->getId());

        $this->assertEquals(1, $subscription->getFrequency());
        $this->assertEquals($frequencyProfile->getId(), $subscription->getFrequencyProfileId());
        $this->assertEquals('flatrate_flatrate', $subscription->getShippingMethod());
        $this->assertEquals('checkmo', $subscription->getPaymentMethod());
    }

    /**
     * @magentoDataFixture frequencyProfileDataFixture
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     */
    public function testChangeSubscriptionFrequency()
    {
        /** @var FrequencyProfile $frequencyProfile */
        $frequencyProfile = ObjectManager::getInstance()
            ->create(FrequencyProfile::class)
            ->getCollection()
            ->getFirstItem();

        /** @var Order $order */
        $order = ObjectManager::getInstance()->create(Order::class);
        $order->loadByIncrementId('100000001');

        /** @var SubscriptionManagement $subscriptionManager */
        $subscriptionManager = ObjectManager::getInstance()->create(SubscriptionManagement::class);
        /** @var Subscription $subscription */
        $subscription = $subscriptionManager->createSubscription($order, 7, $frequencyProfile->getId());
        /** @var Subscription $updatedSubscription */
        $updatedSubscription = $subscriptionManager->changeFrequency(1, (int) $subscription->getId(), 365);

        $this->assertSame(365, $updatedSubscription->getFrequency());
    }
}
