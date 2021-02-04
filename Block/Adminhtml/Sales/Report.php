<?php

declare(strict_types=1);

namespace PayPal\Subscription\Block\Adminhtml\Sales;

class Report extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Reports::report/grid/container.phtml';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_blockGroup = 'PayPal_Subscription';
        $this->_controller = 'adminhtml_sales_report';
        $this->_headerText = __('Subscription Report');
        parent::_construct();

        $this->buttonList->remove('add');
        $this->addButton(
            'filter_form_submit',
            [
                'label' => __('Show Report'),
                'onclick' => 'filterFormSubmit()',
                'class' => 'primary'
            ]
        );
    }

    /**
     * Get filter URL
     *
     * @return string
     */
    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/report', ['_current' => true]);
    }
}
