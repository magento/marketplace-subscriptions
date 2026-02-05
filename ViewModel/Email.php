<?php
declare(strict_types=1);

namespace PayPal\Subscription\ViewModel;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use PayPal\Subscription\Api\Data\SubscriptionItemInterface;
use PayPal\Subscription\Helper\Data;

class Email implements ArgumentInterface
{
    /**
     * @var Data
     */
    private $subscriptionHelper;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var array
     */
    private $paymentTokenRenderers;

    /**
     * @var PricingHelper
     */
    private $pricingHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Email constructor.
     *
     * @param Data $subscriptionHelper
     * @param LayoutInterface $layout
     * @param PricingHelper $pricingHelper
     * @param ProductRepositoryInterface $productRepository
     * @param array $paymentTokenRenderers
     */
    public function __construct(
        Data $subscriptionHelper,
        LayoutInterface $layout,
        PricingHelper $pricingHelper,
        ProductRepositoryInterface $productRepository,
        array $paymentTokenRenderers = []
    ) {
        $this->subscriptionHelper = $subscriptionHelper;
        $this->layout = $layout;
        $this->pricingHelper = $pricingHelper;
        $this->productRepository = $productRepository;
        $this->paymentTokenRenderers = $paymentTokenRenderers;
    }

    /**
     * Format price
     *
     * @param float $price
     * @return string
     */
    public function formatPrice(float $price): string
    {
        return $this->pricingHelper->currency(
            $price,
            true,
            false
        );
    }

    /**
     * Return Product name from product associated to subscription item
     *
     * @param SubscriptionItemInterface $subscriptionItem
     * @return string
     */
    public function getProductName(
        SubscriptionItemInterface $subscriptionItem
    ): string {
        $productName = '';
        try {
            $product = $this->productRepository->getById($subscriptionItem->getProductId());
            return $product->getName() ?: '';
        } catch (NoSuchEntityException $e) {
            return $productName;
        }
    }

    /**
     * Return Payment Token renderer HTML string
     *
     * @param PaymentTokenInterface $paymentToken
     * @return string
     */
    public function getTokenHtml(
        PaymentTokenInterface $paymentToken
    ): string {
        $paymentTokenHtml = '';
        $tokenTypeRenderer = $this->paymentTokenRenderers[$paymentToken->getPaymentMethodCode()] ?? null;
        if ($tokenTypeRenderer !== null) {
            $tokenTypeRendererTemplate = $tokenTypeRenderer['template'] ?? null;
            if ($tokenTypeRendererTemplate !== null) {
                $tokenTypeRendererClass = $tokenTypeRenderer['class'] ?? Template::class;
                /** @var Template $tokenRenderer */
                $tokenRenderer = $this->layout->createBlock(
                    $tokenTypeRendererClass
                );
                $tokenRendererData = $tokenRenderer['data'] ?? [];
                $tokenRendererData['payment'] = $paymentToken;
                $tokenRenderer->addData($tokenRendererData);
                $tokenRenderer->setTemplate($tokenTypeRendererTemplate);
                $paymentTokenHtml = $tokenRenderer->toHtml();
            }
        }
        return $paymentTokenHtml;
    }

    /**
     * Get bundle data
     *
     * @param SubscriptionItemInterface $subscriptionItem
     * @return ?array
     */
    public function getBundleData(SubscriptionItemInterface $subscriptionItem): ?array
    {
        try {
            $product = $this->productRepository->getById($subscriptionItem->getProductId());
            $bundleData = $this->subscriptionHelper->getBundleData($product);
            return empty($bundleData) ? null : $bundleData;
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get selection string
     *
     * @param array $selectionData
     * @return string
     */
    public function getSelectionString(array $selectionData): string
    {
        if (empty($selectionData) ||
            !isset(
                $selectionData['quantity'],
                $selectionData['sku'],
                $selectionData['name'],
                $selectionData['selection_price']
            )
        ) {
            return '';
        }

        return sprintf(
            '%d x %s %s',
            (int)$selectionData['quantity'],
            $selectionData['name'],
            $selectionData['selection_price']
        );
    }
}
