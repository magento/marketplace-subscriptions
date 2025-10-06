<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Controller\Adminhtml\Report\Sales;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Reports\Controller\Adminhtml\Report\Sales;
use PayPal\Subscription\Model\Flag;

class Report extends Sales implements ActionInterface, HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * Subscription report
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->_showLastExecutionTime(Flag::REPORT_FLAG_CODE, 'subscriptionreport');

        $this->_initAction()->_setActiveMenu(
            'PayPal_Subscription::subscription_report'
        )->_addBreadcrumb(
            __('Subscription Report'),
            __('Subscription Report')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Subscription Report'));

        $gridBlock = $this->_view->getLayout()->getBlock('adminhtml_sales_report.grid');
        $filterFormBlock = $this->_view->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction([$gridBlock, $filterFormBlock]);

        $this->_view->renderLayout();
    }
}
