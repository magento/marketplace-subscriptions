<?php

declare(strict_types=1);

namespace PayPal\Subscription\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;

class SubscriptionOnly extends AbstractModifier
{
    private const SUBSCRIPTION_OPTIONS = [
        SubscriptionHelper::SUB_ONLY,
        SubscriptionHelper::SUB_PRICE_TYPE,
        SubscriptionHelper::SUB_PRICE_VALUE,
        SubscriptionHelper::SUB_FREQ_PROF
    ];

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * SubscriptionOnly constructor.
     *
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data): array
    {
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta): array
    {
        foreach (self::SUBSCRIPTION_OPTIONS as $option) {
            $meta = $this->customiseField($meta, $option);
        }
        return $meta;
    }

    /**
     * @param array $meta
     * @param string $option
     * @return array
     */
    private function customiseField(array $meta, string $option): array
    {
        $path = $this->arrayManager->findPath(
            $option,
            $meta,
            null,
            'children'
        );

        if ($path) {

            $mergeArray = [
                'imports' => [
                    'visible' => 'ns = ${ $.ns }, index = ' . SubscriptionHelper::SUB_AVAILABLE . ':checked'
                ]
            ];

            if ($option === SubscriptionHelper::SUB_FREQ_PROF) {
                $mergeArray['validation'] = [
                    'required-entry' => 'ns = ${ $.ns }, index = ' . SubscriptionHelper::SUB_AVAILABLE . ':checked',
                ];
            }

            $meta = $this->arrayManager->merge(
                $path . static::META_CONFIG_PATH,
                $meta,
                $mergeArray
            );
        }

        return $meta;
    }
}
