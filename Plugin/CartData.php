<?php
declare(strict_types=1);

namespace PayPal\Subscription\Plugin;

use Magento\Checkout\CustomerData\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;

/**
 * Plugin to add masked quote id to cart data if guest≥
 */
class CartData
{
    /**
     * @var Session
     */
    private Session $checkoutSession;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private QuoteIdToMaskedQuoteIdInterface $maskedQuote;

    /**
     * CartData constructor.
     * @param Session $checkoutSession
     * @param QuoteIdToMaskedQuoteIdInterface $maskedQuote
     */
    public function __construct(
        Session $checkoutSession,
        QuoteIdToMaskedQuoteIdInterface $maskedQuote
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->maskedQuote = $maskedQuote;
    }

    /**
     * Get section data
     *
     * @param Cart $subject
     * @param $result
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterGetSectionData(Cart $subject, $result): array
    {
        $quote = $this->checkoutSession->getQuote();
        $quoteId = $quote ?
            (int) $quote->getId() :
            null;
        if ($quote &&
            !$quote->getCustomerId() &&
            $quoteId != null
        ) {
            $maskedId = $this->maskedQuote->execute((int) $quote->getId());
            $result['guest_masked_id'] = $maskedId;
        }
        return $result;
    }
}

