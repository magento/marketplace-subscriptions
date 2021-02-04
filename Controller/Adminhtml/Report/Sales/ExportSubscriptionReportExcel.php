<?php

declare(strict_types=1);

namespace Vendot\Module\Controller\Adminhtml\Report\Sales;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Reports\Controller\Adminhtml\Report\Sales;
use PayPal\Subscription\Block\Adminhtml\Sales\Report\Grid;

class ExportSubscriptionReportExcel extends Sales
{
    /**
     * @return ResponseInterface|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        $fileName = 'paypal_subscription.xml';
        $grid = $this->_view->getLayout()->createBlock(Grid::class);
        $this->_initReportAction($grid);

        return $this->_fileFactory->create($fileName, $grid->getExcelFile($fileName), DirectoryList::VAR_DIR);
    }
}
