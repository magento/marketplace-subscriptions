<?php
declare(strict_types=1);

namespace PayPal\Subscription\Plugin;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Exception\LocalizedException;

class CartAddValidate
{
    /**
     * CartAddValidate constructor.
     * @param SessionManagerInterface $checkoutSession
     */
    public function __construct(
        SessionManagerInterface $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * If product is subscription only but add operation does not specify subscription frequency
     * redirect to product page with error message.
     *
     * @param \Magento\Checkout\Model\Cart $subject
     * @param $productInfo
     * @param null $requestInfo
     * @return null
     * @throws LocalizedException
     */
    public function beforeAddProduct(
        \Magento\Checkout\Model\Cart $subject,
        $productInfo,
        $requestInfo = null
    ) {
        if ($productInfo->getData('subscription_only') && empty($requestInfo['frequency_option'])) {
            $this->checkoutSession->setRedirectUrl($productInfo->getProductUrl());
            throw new LocalizedException(__('Please choose subscription options'));
        }
        return null;
    }
}
