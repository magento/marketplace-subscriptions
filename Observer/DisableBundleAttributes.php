<?php

declare(strict_types=1);

namespace PayPal\Subscription\Observer;

use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use PayPal\Subscription\Helper\Data;
use PayPal\Subscription\Model\ConfigurationInterface;

class DisableBundleAttributes implements ObserverInterface
{
    /**
     * @param ConfigurationInterface $configuration
     */
    public function __construct(private readonly ConfigurationInterface $configuration)
    {
    }

    /**
     * Disable bundle attributes
     *
     * @param Observer $observer
     * @return void
     * @throws CouldNotSaveException
     */
    public function execute(Observer $observer): void
    {
        $product = $observer->getData('product');
        if ($this->configuration->getActive() === false || !$this->isProductBundle($product)) {
            return;
        }

        if ((bool)$product->getData(Data::SUB_AVAILABLE) === true && !$this->bundleEligibleForSubscription($product)) {
            throw new CouldNotSaveException(__(
                "Bundle configuration is not eligible for subscriptions.
                 Please ensure bundle is fixed price, has one product per option and contains no user defined options."
            ));
        }
    }

    /**
     * Is product bundle
     *
     * @param ProductInterface|null $product
     * @return bool
     */
    private function isProductBundle(?ProductInterface $product): bool
    {
        return $product instanceof ProductInterface && $product->getTypeId() === Type::TYPE_CODE;
    }

    /**
     * Return false IF bundle is dynamically priced OR bundle has more than one selection per option OR
     *
     * Bundle contains ANY selections that are user defined === true.
     *
     * @param ProductInterface $product
     * @return bool
     */
    private function bundleEligibleForSubscription(ProductInterface $product): bool
    {
        if ((int)$product->getPriceType() === Price::PRICE_TYPE_DYNAMIC) {
            return false;
        }

        foreach ($product->getData('bundle_selections_data') as $optionData) {
            if (count($optionData) > 1) {
                return false;
            }
            foreach ($optionData as $selection) {
                if ((bool)$selection['selection_can_change_qty'] === true) {
                    return false;
                }
            }
        }

        return true;
    }
}
