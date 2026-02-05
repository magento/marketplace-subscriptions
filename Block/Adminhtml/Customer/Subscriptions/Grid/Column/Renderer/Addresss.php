<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Block\Adminhtml\Customer\Subscriptions\Grid\Column\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\Text;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\SerializerInterface;

class Addresss extends Text
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Addresss constructor
     *
     * @param Context $context
     * @param SerializerInterface $serializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        SerializerInterface $serializer,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        $this->serializer = $serializer;
    }

    /**
     * Return Address String for Grid from JSON string
     *
     * @param DataObject $row
     * @return string
     */
    public function _getValue(
        DataObject $row
    ): string {
        $value = parent::_getValue($row) ?: '';
        if (is_string($value)) {
            $addressArray = json_decode($value, true) ?: [];
            $value = implode(
                ', </br>',
                $addressArray
            );
        }
        return $value;
    }
}
