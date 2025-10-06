<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Block\Customer\View;

use PayPal\Subscription\Block\Customer\View;
use Magento\Theme\Block\Html\Pager;
use Magento\Framework\Exception\LocalizedException;

/**
 * @api
 * @since 100.0.2
 */
class Releases extends View
{
    /**
     * Prepare layout for subscription releases
     *
     * @return self
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getSubscriptionReleases($this->getSubscriptionId())) {
            $pager = $this->getLayout()
                ->createBlock(Pager::class, 'subscription.customer.pager.releases')
                ->setAvailableLimit([5 => 5, 10 => 10, 15 => 15, 20 => 20])
                ->setLimitVarName('release-limit')
                ->setPageVarName('release-page')
                ->setShowPerPage(true)
                ->setCollection(
                    $this->getSubscriptionReleases($this->getSubscriptionId())
                );
            $this->setChild('pager', $pager);
            $this->getSubscriptionReleases($this->getSubscriptionId())->load();
        }

        return $this;
    }

    /**
     * Get pager html
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
