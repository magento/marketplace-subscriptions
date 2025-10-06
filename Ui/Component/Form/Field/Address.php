<?php
declare(strict_types=1);

namespace PayPal\Subscription\Ui\Component\Form\Field;

use InvalidArgumentException;
use Magento\Framework\Serialize\SerializerInterface;

class Address
{
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

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
     * Format address
     *
     * @param string $address
     * @return string|array
     */
    public function format($address): string|array
    {
        return $this->unserializeAndFormatAddress($address);
    }

    /**
     * Un-serialize and format the address
     *
     * @param string $address
     * @return string|array
     */
    private function unserializeAndFormatAddress($address): string|array
    {
        try {
            $addressArray = $this->serializer->unserialize($address);

            $addressArray = array_filter($addressArray);
            if (count($addressArray) == 0) {

                return [];
            }
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
