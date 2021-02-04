<?php

declare(strict_types=1);

namespace PayPal\Subscription\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use PayPal\Subscription\Model\Config\Source\Subscription\FrequencyProfile;
use PayPal\Subscription\Model\Config\Source\Subscription\PriceType;
use Zend_Validate_Exception;

/**
 * Class AddProductSubscriptionAttributes
 *
 * Add custom subscription attributes to products
 */
class AddProductSubscriptionAttributes implements DataPatchInterface
{
    private const SUBSCRIPTION_ATTR_GROUP = 'Subscription';
    private const SUBSCRIPTION_AVAILABLE = 'subscription_available';
    private const SUBSCRIPTION_ONLY = 'subscription_only';
    private const SUBSCRIPTION_PRICE_TYPE = 'subscription_price_type';
    private const SUBSCRIPTION_PRICE_VALUE = 'subscription_price_value';
    private const SUBSCRIPTION_FREQUENCY_PROFILE = 'subscription_frequency_profile';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup, EavSetupFactory $eavSetupFactory)
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    public function apply()
    {
        $this->eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $this->eavSetup->addAttribute(Product::ENTITY, self::SUBSCRIPTION_AVAILABLE, [
            'type' => 'int',
            'label' => 'Is available as subscription?',
            'input' => 'boolean',
            'source' => Boolean::class,
            'default' => 0,
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'required' => false,
            'sort_order' => 90
        ]);
        $this->eavSetup->addAttribute(Product::ENTITY, self::SUBSCRIPTION_ONLY, [
            'type' => 'int',
            'label' => 'Is available only as subscription?',
            'input' => 'boolean',
            'source' => Boolean::class,
            'default' => 0,
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'required' => false,
            'sort_order' => 100,
            'used_in_product_listing' => true,
        ]);
        $this->eavSetup->addAttribute(Product::ENTITY, self::SUBSCRIPTION_PRICE_TYPE, [
            'type' => 'int',
            'label' => 'Subscription price type',
            'input' => 'select',
            'source' => PriceType::class,
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'required' => false,
            'sort_order' => 110,
            'note' => 'Fixed: Override base price. Discount: Percentage discount of base price.'
        ]);
        $this->eavSetup->addAttribute(Product::ENTITY, self::SUBSCRIPTION_PRICE_VALUE, [
            'type' => 'decimal',
            'label' => 'Subscription price value',
            'input' => 'text',
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'required' => false,
            'sort_order' => 120,
            'frontend_class' => 'validate-number',
            'note' => 'Price amount if type is "fixed". Percentage if type is "discount"'
        ]);
        $this->eavSetup->addAttribute(Product::ENTITY, self::SUBSCRIPTION_FREQUENCY_PROFILE, [
            'type' => 'int',
            'label' => 'Frequency Profile',
            'input' => 'select',
            'source' => FrequencyProfile::class,
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'required' => false,
            'sort_order' => 120,
        ]);

        $entityTypeId = $this->eavSetup->getEntityTypeId(Product::ENTITY);
        $attributeSetIds = $this->eavSetup->getAllAttributeSetIds($entityTypeId);

        foreach ($attributeSetIds as $attributeSetId) {
            $this->eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, self::SUBSCRIPTION_ATTR_GROUP, 100);
            $attributeGroupId = $this->eavSetup->getAttributeGroupId(
                $entityTypeId,
                $attributeSetId,
                self::SUBSCRIPTION_ATTR_GROUP
            );

            $this->addAttributesToGroup(
                $entityTypeId,
                $attributeSetId,
                $attributeGroupId,
                [
                    self::SUBSCRIPTION_AVAILABLE,
                    self::SUBSCRIPTION_ONLY,
                    self::SUBSCRIPTION_PRICE_TYPE,
                    self::SUBSCRIPTION_PRICE_VALUE,
                    self::SUBSCRIPTION_FREQUENCY_PROFILE
                ]
            );
        }
    }

    /**
     * @param $entityTypeId
     * @param $attributeSetId
     * @param $attributeGroupId
     * @param array $attributes
     */
    private function addAttributesToGroup($entityTypeId, $attributeSetId, $attributeGroupId, array $attributes): void
    {
        foreach ($attributes as $key => $attribute) {
            $attributeId = $this->eavSetup->getAttributeId($entityTypeId, $attribute);
            $this->eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attributeId, $key);
        }
    }
}
