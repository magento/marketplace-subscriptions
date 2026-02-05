<?php
declare(strict_types=1);

namespace PayPal\Subscription\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use PayPal\Subscription\Model\Product\Attribute\Source\RecommendedFrequency as RecommendedFrequencySource;
use PayPal\Subscription\Model\Product\Attribute\Backend\RecommendedFrequency as RecommendedFrequencyBackend;
use PayPal\Subscription\Setup\Patch\Data\AddProductSubscriptionAttributes;

class InstallRecommendedFrequencyAttributes implements DataPatchInterface
{
    public const RECOMMENDED_FREQUENCY_ATTRIBUTE_CODE = 'recommended_frequency';
    public const RECOMMENDED_FREQUENCY_REASON_ATTRIBUTE_CODE = 'recommended_frequency_reason';

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * InstallRecommendedFrequencyAttributes constructor
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritDoc
     */
    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create([
            'setup' => $this->moduleDataSetup
        ]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            self::RECOMMENDED_FREQUENCY_ATTRIBUTE_CODE,
            [
                'type' => 'int',
                'label' => 'Recommended Frequency',
                'input' => 'select',
                'backend' => RecommendedFrequencyBackend::class,
                'source' => RecommendedFrequencySource::class,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'sort_order' => 150
            ]
        );
        $eavSetup->addAttribute(
            Product::ENTITY,
            self::RECOMMENDED_FREQUENCY_REASON_ATTRIBUTE_CODE,
            [
                'type' => 'text',
                'label' => 'Recommendation Text',
                'input' => 'textarea',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'sort_order' => 160,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true
            ]
        );
        $productEntityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);
        $productAttributeSetIds = $eavSetup->getAllAttributeSetIds($productEntityTypeId);
        foreach ($productAttributeSetIds as $productAttributeSetId) {
            $eavSetup->addAttributeGroup(
                $productEntityTypeId,
                $productAttributeSetId,
                AddProductSubscriptionAttributes::SUBSCRIPTION_ATTR_GROUP
            );
            $recommendedFrequencyGroupId = $eavSetup->getAttributeGroupId(
                $productEntityTypeId,
                $productAttributeSetId,
                AddProductSubscriptionAttributes::SUBSCRIPTION_ATTR_GROUP
            );
            $eavSetup->addAttributeToGroup(
                $productEntityTypeId,
                $productAttributeSetId,
                $recommendedFrequencyGroupId,
                $eavSetup->getAttributeId(
                    $productEntityTypeId,
                    self::RECOMMENDED_FREQUENCY_ATTRIBUTE_CODE
                )
            );
            $eavSetup->addAttributeToGroup(
                $productEntityTypeId,
                $productAttributeSetId,
                $recommendedFrequencyGroupId,
                $eavSetup->getAttributeId(
                    $productEntityTypeId,
                    self::RECOMMENDED_FREQUENCY_REASON_ATTRIBUTE_CODE
                )
            );
        }
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [
            AddProductSubscriptionAttributes::class
        ];
    }
}
