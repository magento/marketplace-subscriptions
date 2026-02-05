<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Block\Adminhtml\Customer\Subscriptions\Grid\Column\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\Text;
use Magento\Framework\DataObject;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\SerializerInterface;
use PayPal\Subscription\Model\Config\Source\Subscription\Status as StatusSource;

class Status extends Text
{
    /**
     * @var StatusSource
     */
    private $status;

    /**
     * Status constructor.
     *
     * @param Context $context
     * @param StatusSource $status
     * @param array $data
     */
    public function __construct(
        Context $context,
        StatusSource $status,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        $this->status = $status;
    }

    /**
     * Return Status String for Grid
     *
     * @param DataObject $row
     * @return string
     */
    public function _getValue(
        DataObject $row
    ): string {
        $status = parent::_getValue($row) ?: 3;
        /** @var Phrase $statusText */
        $statusText = $this->status->getOptionText($status);
        return $statusText->getText();
    }
}
