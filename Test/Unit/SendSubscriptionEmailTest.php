<?php

declare(strict_types=1);

namespace PayPal\Subscription\Test\Unit;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TransportInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManager;
use PayPal\Subscription\Model\Email\Subscription as SubscriptionEmail;
use PayPal\Subscription\Model\ResourceModel\SubscriptionItem\CollectionFactory;
use PayPal\Subscription\Model\Subscription;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SendSubscriptionEmailTest extends TestCase
{
    /**
     * @var TransportBuilder|MockObject
     */
    private $transportBuilder;

    /**
     * @var TransportInterface|MockObject
     */
    private $transportInterface;

    /**
     * @var StoreManager|MockObject
     */
    private $storeManager;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeInterface;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var Subscription|MockObject
     */
    private $subscription;

    /**
     * @var CustomerInterface|MockObject
     */
    private $customer;

    /**
     * @var SubscriptionEmail
     */
    private $subscriptionEmail;

    /**
     * @var CollectionFactory|MockObject
     */
    private $subscriptionItems;

    public function setUp()
    {
        $this->transportBuilder = $this->createMock(TransportBuilder::class);
        $this->transportInterface = $this->createMock(TransportInterface::class);
        $this->transportBuilder->method('getTransport')->willReturn($this->transportInterface);

        $this->storeManager = $this->createMock(StoreManager::class);
        $this->storeInterface = $this->createMock(StoreInterface::class);
        $this->storeManager->method('getStore')->willReturn($this->storeInterface);
        $this->storeInterface->method('getId')->willReturn(1);

        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->scopeConfig->method('getValue')->willReturnMap([
            ['general/store_information/name', 'Store Name'],
            ['trans_email/ident_general/email', 'no-reply@my-store.test']
        ]);

        $this->subscriptionItems = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subscription = $this->createMock(Subscription::class);

        $this->customer = $this->createMock(CustomerInterface::class);
        $this->customer->method('getEmail')->willReturn('somewhere@example.test');
        $this->customer->method('getFirstname')->willReturn('Joe');
        $this->customer->method('getLastname')->willReturn('Bloggs');
    }

    public function testSendEmail()
    {
        $this->subscriptionEmail = $this->getMockBuilder(SubscriptionEmail::class)
            ->setConstructorArgs([
                $this->transportBuilder,
                $this->storeManager,
                $this->scopeConfig,
                $this->subscriptionItems
            ])
            ->setMethodsExcept(['sendEmail', 'send'])
            ->getMock();
        $this->subscriptionEmail->expects($this->once())
            ->method('getSubscriptionItems')
            ->with($this->anything());

        $result = $this->subscriptionEmail->send($this->subscription, $this->customer);
        $this->assertSame('Joe Bloggs', $result['data']['customer_name']);
    }
}
