<?php

declare(strict_types=1);

namespace PayPal\Subscription\Plugin\Magento\Reports\Model\ResourceModel\Refresh;

use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\FlagFactory;
use PayPal\Subscription\Model\Flag;

class Collection extends \Magento\Framework\Data\Collection
{
    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var FlagFactory
     */
    protected $reportsFlagFactory;

    /**
     * @param EntityFactory $entityFactory
     * @param TimezoneInterface $localeDate
     * @param FlagFactory $reportsFlagFactory
     */
    public function __construct(
        EntityFactory $entityFactory,
        TimezoneInterface $localeDate,
        FlagFactory $reportsFlagFactory
    ) {
        parent::__construct($entityFactory);
        $this->localeDate = $localeDate;
        $this->reportsFlagFactory = $reportsFlagFactory;
    }

    /**
     * Get if updated
     *
     * @param string $reportCode
     * @return string
     * @throws LocalizedException
     */
    protected function getUpdatedAt($reportCode)
    {
        $flag = $this->reportsFlagFactory->create()->setReportFlagCode($reportCode)->loadSelf();
        return $flag->hasData() ? $flag->getLastUpdate() : '';
    }

    /**
     * Load data
     *
     * @param $subject
     * @param $result
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     * @throws LocalizedException
     */
    public function afterLoadData($subject, $result, $printQuery = false, $logQuery = false)
    {
        if (!count($this->_items)) {
            $data = [
                [
                    'id' => 'subscriptionreport',
                    'report' => __('PayPal Subscription Report'),
                    'comment' => __('PayPal Subscription Report'),
                    'updated_at' => $this->getUpdatedAt(
                        Flag::REPORT_FLAG_CODE
                    )
                ],
            ];
            foreach ($data as $value) {
                $item = new DataObject();
                $item->setData($value);
                $this->addItem($item);
                $subject->addItem($item);
            }
        }
        return $subject;
    }
}
