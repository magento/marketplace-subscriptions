<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model\Ui\Vault;

use Magento\Checkout\Model\Cart;
use PayPal\Subscription\Helper\Data as SubscriptionHelper;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

class ConfigProvider implements ConfigProviderInterface
{
    public const CODE = 'braintree_paypal_vault';

    /**
     * @var Cart
     */
    private $cart;

    /**
     * ConfigProvider constructor.
     *
     * @param Cart $cart
     */
    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getConfig(): array
    {
        $items = $this->cart->getItems();
        foreach ($items as $item) {
            $isSubscriptionOption = $item->getOptionByCode(SubscriptionHelper::IS_SUBSCRIPTION);
            if ($isSubscriptionOption !== null) {
                if ($isSubscriptionOption->getValue() === '1') {
                    return [
                        'vault' => [
                            self::CODE => [
                                'is_enabled' => true
                            ]
                        ]
                    ];
                }
            }
        }

        return [];
    }
}
