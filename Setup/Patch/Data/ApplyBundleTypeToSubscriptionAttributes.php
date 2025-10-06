<?php
declare(strict_types=1);

namespace PayPal\Subscription\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class UpdateProductSubscriptionAttributes
 *
 * Update custom subscription attributes to products
 */
class ApplyBundleTypeToSubscriptionAttributes implements DataPatchInterface
{
    public const SUBSCRIPTION_AVAILABLE = 'subscription_available';
    public const SUBSCRIPTION_ONLY = 'subscription_only';
    public const SUBSCRIPTION_PRICE_TYPE = 'subscription_price_type';
    public const SUBSCRIPTION_PRICE_VALUE = 'subscription_price_value';
    public const SUBSCRIPTION_FREQUENCY_PROFILE = 'subscription_frequency_profile';
    public const RECOMMENDED_FREQUENCY_ATTRIBUTE_CODE = 'recommended_frequency';

    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private EavSetupFactory $eavSetupFactory;

    /**
     * @var EavSetup
     */
    private EavSetup $eavSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [
            AddProductSubscriptionAttributes::class,
            InstallRecommendedFrequencyAttributes::class,
            UpdateProductSubscriptionAttributes::class
        ];
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Apply bundle type to subscription attributes
     *
     * @return void
     */
    public function apply(): void
    {
        $this->eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $attributes = [
            self::SUBSCRIPTION_AVAILABLE,
            self::SUBSCRIPTION_ONLY,
            self::SUBSCRIPTION_PRICE_TYPE,
            self::SUBSCRIPTION_PRICE_VALUE,
            self::SUBSCRIPTION_FREQUENCY_PROFILE,
            self::RECOMMENDED_FREQUENCY_ATTRIBUTE_CODE
        ];
        foreach ($attributes as $attribute) {
            $this->eavSetup->updateAttribute(
                Product::ENTITY,
                $attribute,
                'apply_to',
                'simple,configurable,downloadable,bundle,virtual'
            );
        }
    }
}
