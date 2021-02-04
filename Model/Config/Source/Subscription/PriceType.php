<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model\Config\Source\Subscription;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class PriceType extends AbstractSource
{

    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions(): array
    {
        return [
            ['label' => __('Fixed Price'), 'value' => 0],
            ['label' => __('Discounted Price'), 'value' => 1]
        ];
    }
}
