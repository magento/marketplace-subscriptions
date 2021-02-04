<?php

declare(strict_types=1);

namespace PayPal\Subscription\Plugin;

use Closure;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\Quote\Item;

class ToOrderItem
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * ToOrderItem constructor.
     *
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param Item\ToOrderItem $subject
     * @param Closure $proceed
     * @param $item
     * @param array $data
     * @return mixed
     */
    public function aroundConvert(
        Item\ToOrderItem $subject,
        Closure $proceed,
        $item,
        $data = []
    ) {
        /**
         * @var \Magento\Sales\Model\Order\Item $orderItem
         * @var Item $item
         */
        $orderItem = $proceed($item, $data);

        $orderItemOptions = [];

        $options = $item->getOptions();

        foreach ($options as $option) {
            $orderItemOptions[$option->getCode()] = $this->serializer->unserialize($option->getValue());
        }

        $orderItem->setProductOptions($orderItemOptions);

        return $orderItem;
    }
}
