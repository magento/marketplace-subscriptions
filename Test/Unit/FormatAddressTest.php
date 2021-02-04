<?php

declare(strict_types=1);

namespace PayPal\Subscription\Test\Unit;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\ResourceModel\Order;
use PayPal\Subscription\Api\Data\SubscriptionInterfaceFactory;
use PayPal\Subscription\Block\Customer\View;
use PayPal\Subscription\Model\ResourceModel\Subscription;
use PayPal\Subscription\Model\ResourceModel\SubscriptionRelease\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FormatAddressTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var SubscriptionInterfaceFactory|MockObject
     */
    private $subscriptionInterface;

    /**
     * @var Subscription|MockObject
     */
    private $subscriptionResource;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializer;

    /**
     * @var View|MockObject
     */
    private $subscriptionView;

    /**
     * @var OrderInterfaceFactory|MockObject
     */
    private $orderInterface;

    /**
     * @var Order|MockObject
     */
    private $orderResource;

    /**
     * @var CollectionFactory|MockObject
     */
    private $subscriptionReleaseCollection;

    protected function setUp()
    {
        $this->context = $this->createMock(Context::class);
        $this->subscriptionInterface = $this->createMock(SubscriptionInterfaceFactory::class);
        $this->subscriptionResource = $this->createMock(Subscription::class);
        $this->orderInterface = $this->createMock(OrderInterfaceFactory::class);
        $this->orderResource = $this->createMock(Order::class);
        $this->subscriptionReleaseCollection = $this->createMock(CollectionFactory::class);
        $this->serializer = new Json();

        $this->subscriptionView = $this->getMockBuilder(View::class)
            ->setConstructorArgs([
                $this->context,
                $this->subscriptionInterface,
                $this->subscriptionResource,
                $this->orderInterface,
                $this->orderResource,
                $this->subscriptionReleaseCollection,
                $this->serializer
            ])
            ->setMethodsExcept(['formatAddress', 'implodeNested'])
            ->getMock();
    }

    public function testFormatAddress()
    {
        $address = '{"street":["Church House", "1 Hanover Street"],"city":"Liverpool","region":"Merseyside","postcode":"L1 3DN","country_id":"GB"}'; // @codingStandardsIgnoreLine
        $expected = 'Church House, 1 Hanover Street, Liverpool, Merseyside, L1 3DN, GB';
        $formattedAddress = $this->subscriptionView->formatAddress($address);
        $this->assertEquals($expected, $formattedAddress);
    }
}
