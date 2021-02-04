<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask;
use PayPal\Subscription\Api\GuestQuoteManagementInterface;
use PayPal\Subscription\Api\QuoteManagementInterface;

class GuestQuoteManagement implements GuestQuoteManagementInterface
{
    /**
     * @var QuoteManagementInterface
     */
    private $quoteManagement;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var QuoteIdMask
     */
    private $quoteIdMaskResource;

    /**
     * GuestQuoteManagement constructor.
     *
     * @param QuoteManagementInterface $quoteManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param QuoteIdMask $quoteIdMaskResource
     */
    public function __construct(
        QuoteManagementInterface $quoteManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteIdMask $quoteIdMaskResource
    ) {
        $this->quoteManagement = $quoteManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteIdMaskResource = $quoteIdMaskResource;
    }

    /**
     * @param string $cartId
     * @param int $quoteItemId
     * @param int $frequency
     * @return CartInterface
     * @throws LocalizedException
     */
    public function changeFrequency(string $cartId, int $quoteItemId, int $frequency): CartInterface
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $this->quoteIdMaskResource->load($quoteIdMask, $cartId, 'masked_id');

        if (!$quoteIdMask->getData()) {
            throw new LocalizedException(__('Unable to fetch quote.'));
        }

        return $this->quoteManagement->changeFrequency($quoteIdMask->getData('quote_id'), $quoteItemId, $frequency);
    }
}
