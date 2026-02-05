<?php
declare(strict_types=1);

namespace PayPal\Subscription\Plugin\Refresh;

use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\FlagFactory;
use Magento\Reports\Model\ResourceModel\Refresh\Collection as ReportsRefreshCollection;
use PayPal\Subscription\Model\Flag;

class Collection extends \Magento\Framework\Data\Collection
{
    /**
     * @var TimezoneInterface
     */
    protected TimezoneInterface $localeDate;

    /**
     * @var FlagFactory
     */
    private FlagFactory $reportsFlagFactory;

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
    protected function getUpdatedAt($reportCode): string
    {
        $flag = $this->reportsFlagFactory->create()->setReportFlagCode($reportCode)->loadSelf();
        return $flag->hasData() ? $flag->getLastUpdate() : '';
    }

    /**
     * Load data
     *
     * @param ReportsRefreshCollection $subject
     * @param ReportsRefreshCollection $result
     * @return ReportsRefreshCollection
     * @throws LocalizedException
     */
    public function afterLoadData(ReportsRefreshCollection $subject, $result): ReportsRefreshCollection
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
