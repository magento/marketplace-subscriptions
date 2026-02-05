<?php
declare(strict_types=1);

namespace PayPal\Subscription\Observer\Adminhtml;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use PayPal\Subscription\Registry\CurrentProduct as CurrentProductRegistry;

class CurrentProduct implements ObserverInterface
{
    /**
     * @var CurrentProductRegistry
     */
    private $currentProduct;

    /**
     * CurrentProduct constructor
     *
     * @param CurrentProductRegistry $currentProduct
     */
    public function __construct(
        CurrentProductRegistry $currentProduct
    ) {
        $this->currentProduct = $currentProduct;
    }

    /**
     * Set current product
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(
        Observer $observer
    ): void {
        $product = $observer->getEvent()->getData('product');
        if ($product) {
            $this->currentProduct->set($product);
        }
    }
}
