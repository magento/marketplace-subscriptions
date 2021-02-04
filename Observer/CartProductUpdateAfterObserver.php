<?php

declare(strict_types=1);

namespace PayPal\Subscription\Observer;

use Exception;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\ResourceModel\Quote\Item\Option as OptionResource;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;

class CartProductUpdateAfterObserver implements ObserverInterface
{
    /**
     * @var Http
     */
    private $request;

    /**
     * @var OptionResource
     */
    private $optionResource;

    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * CartProductUpdateAfterObserver constructor.
     *
     * @param Http $request
     * @param OptionResource $optionResource
     * @param SubscriptionHelper $subscriptionHelper
     */
    public function __construct(
        Http $request,
        OptionResource $optionResource,
        SubscriptionHelper $subscriptionHelper
    ) {
        $this->request = $request;
        $this->optionResource = $optionResource;
        $this->subscriptionHelper = $subscriptionHelper;
    }

    /**
     * @param Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        if (!$this->subscriptionHelper->isActive()) {
            return;
        }

        $data = $this->request->getPost('cart');

        /** @var Cart $cart */
        $cart = $observer->getData('cart');
        /** @var Quote $quote */
        $quote = $cart->getQuote();
        /** @var CartItemInterface[] $items */
        $items = $quote->getItems();
        /** @var Item $item */
        foreach ($items as $item) {
            $item = $item->getParentItem() ?? $item;
            $options = $item->getOptions();
            foreach ($options as $option) {
                if ($option->getCode() === SubscriptionHelper::FREQ_OPT_INTERVAL) {
                    $option->setValue($data[$item->getItemId()]['frequency_option']);
                    try {
                        $this->optionResource->save($option);
                        break;
                    } catch (AlreadyExistsException | Exception $e) {
                        throw new LocalizedException(__('Unable to update subscription'));
                    }
                }
            }
        }
    }
}
