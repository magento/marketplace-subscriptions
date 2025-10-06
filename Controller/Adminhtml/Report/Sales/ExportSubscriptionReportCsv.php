<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace PayPal\Subscription\Controller\Adminhtml\Report\Sales;

use Exception;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Reports\Controller\Adminhtml\Report\Sales;
use PayPal\Subscription\Block\Adminhtml\Sales\Report\Grid;

class ExportSubscriptionReportCsv extends Sales implements
    ActionInterface,
    HttpGetActionInterface,
    HttpPostActionInterface
{
    /**
     * Export subscription report csv
     *
     * @return ResponseInterface|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        $fileName = 'paypal_subscription.csv';
        $grid = $this->_view->getLayout()->createBlock(Grid::class);
        $this->_initReportAction($grid);

        return $this->_fileFactory->create(
            $fileName,
            $grid->getCsvFile(),
            DirectoryList::VAR_DIR
        );
    }
}
