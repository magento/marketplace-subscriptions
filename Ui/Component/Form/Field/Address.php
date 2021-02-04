<?php

declare(strict_types=1);

namespace PayPal\Subscription\Ui\Component\Form\Field;

use InvalidArgumentException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Address
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Address constructor.
     * @param SerializerInterface $serializer
     */
    public function __construct(
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * @param $address
     * @return string
     */
    public function format($address)
    {
        return $this->unserializeAndFormatAddress($address);
    }

    /**
     * @param $address
     * @return string
     */
    private function unserializeAndFormatAddress($address)
    {
        try {
            $addressArray = $this->serializer->unserialize($address);
            $addressArray = array_filter($addressArray);

            $addressArray = [
                'name' => sprintf('%s %s', $addressArray['firstname'], $addressArray['lastname'])
                ] + $addressArray;
            unset($addressArray['firstname'], $addressArray['lastname']);

            // Implode the nested `street` array
            $addressArray['street'] = implode(', ', $addressArray['street']);

            return implode(', ', $addressArray);
        } catch (InvalidArgumentException $e) {
            return $address;
        }
    }
}
