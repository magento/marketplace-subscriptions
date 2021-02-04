<?php

declare(strict_types=1);

namespace PayPal\Subscription\Block\Adminhtml\Subscriptions\Edit;

/**
 * Provide Save button config data.
 */
class SaveButton extends GenericButton
{
    /**
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('Save Subscription'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 90
        ];
    }
}
