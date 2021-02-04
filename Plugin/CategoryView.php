<?php
declare(strict_types=1);

namespace PayPal\Subscription\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Request\Http;

class CategoryView
{
    /** @var Http */
    private $request;
    /** @var null|string Full action name of request being processed  */
    private $currentAction;
    /** @var string[] Actions that this check should be executed on */
    private $actions = [];

    /**
     * CategoryView constructor.
     * @param Http $request
     * @param array $actions
     */
    public function __construct(
        Http $request,
        array $actions = []
    ) {
        $this->request = $request;
        $this->actions = $actions;
    }

    /**
     * Products that are configured as `subscription_only` should not have the `Add to Cart` button shown
     * in products listings.
     *
     * @param Product $subject
     * @param $result
     * @return bool
     */
    public function afterIsSaleable(Product $subject, $result): bool
    {
        if ($result && $this->isAppropriateAction() && $subject->getSubscriptionOnly()) {
            return false;
        }
        return $result;
    }

    /**
     * @return bool
     */
    private function isAppropriateAction(): bool
    {
        if (null === $this->currentAction) {
            $this->currentAction = $this->request->getFullActionName();
        }
        return in_array($this->currentAction, $this->actions);
    }
}
