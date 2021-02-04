<?php

declare(strict_types=1);

namespace PayPal\Subscription\Model\ResourceModel\Report;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone\Validator;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\FlagFactory;
use Magento\Sales\Model\ResourceModel\Report\AbstractReport;
use PayPal\Subscription\Model\Flag;
use PayPal\Subscription\Model\ResourceModel\SubscriptionItem\CollectionFactory as SubscriptionItemCollectionFactory;
use Psr\Log\LoggerInterface;
use Zend_Db_Expr;

class Report extends AbstractReport
{
    const AGGREGATION_DAILY = 'paypal_subs_report_aggregated_daily';
    const AGGREGATION_MONTHLY = 'paypal_subs_report_aggregated_monthly';
    const AGGREGATION_YEARLY = 'paypal_subs_report_aggregated_yearly';

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var SubscriptionItemCollectionFactory
     */
    private $subscriptionItemCollectionFactory;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param TimezoneInterface $localeDate
     * @param FlagFactory $reportsFlagFactory
     * @param Validator $timezoneValidator
     * @param DateTime $dateTime
     * @param ResourceConnection $resource
     * @param TimezoneInterface $timezone
     * @param SubscriptionItemCollectionFactory $subscriptionItemCollectionFactory
     * @param ProductRepositoryInterface $productRepository
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        TimezoneInterface $localeDate,
        FlagFactory $reportsFlagFactory,
        Validator $timezoneValidator,
        DateTime $dateTime,
        ResourceConnection $resource,
        TimezoneInterface $timezone,
        SubscriptionItemCollectionFactory $subscriptionItemCollectionFactory,
        ProductRepositoryInterface $productRepository,
        $connectionName = null
    ) {
        parent::__construct(
            $context,
            $logger,
            $localeDate,
            $reportsFlagFactory,
            $timezoneValidator,
            $dateTime,
            $connectionName
        );

        $this->resource = $resource;
        $this->timezone = $timezone;
        $this->subscriptionItemCollectionFactory = $subscriptionItemCollectionFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::AGGREGATION_DAILY, 'id');
    }

    /**
     * Aggregate Orders data by order created at
     *
     * @param string|int|\DateTime|array|null $from
     * @param string|int|\DateTime|array|null $to
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function aggregate($from = null, $to = null)
    {
        $mainTable = $this->getMainTable();
        $connection = $this->getConnection();

        $this->truncateTable();

        $insertBatches = [];

        $collection = $this->subscriptionItemCollectionFactory->create();
        $collection->addExpressionFieldToSelect('num_subscriptions', 'SUM({{qty}})', 'qty')
            ->addExpressionFieldToSelect('created', 'DATE({{created_at}})', 'created_at')
            ->getSelect()->group('sku')->group('created');

        if ($collection) {
            foreach ($collection as $info) {
                $product = $this->productRepository->getById($info['product_id']);
                $insertBatches[] = [
                    'period' => date('Y-m-d', strtotime($info['created_at'])),
                    'store_id' => $product->getStoreId(),
                    'product_id' => (int) $info['product_id'],
                    'product_sku' => $info['sku'],
                    'product_name' => $product->getName(),
                    'num_subscriptions' => (int) $info['num_subscriptions']
                ];
            }
        }

        $tableName = $this->resource->getTableName(self::AGGREGATION_DAILY);

        foreach (array_chunk($insertBatches, 100) as $batch) {
            $connection->insertMultiple($tableName, $batch);
        }

        $this->updateReportMonthlyYearly(
            $connection,
            'month',
            'num_subscriptions',
            $mainTable,
            $this->getTable(self::AGGREGATION_MONTHLY)
        );
        $this->updateReportMonthlyYearly(
            $connection,
            'year',
            'num_subscriptions',
            $mainTable,
            $this->getTable(self::AGGREGATION_YEARLY)
        );

        $this->_setFlagData(Flag::REPORT_FLAG_CODE);

        return $this;
    }

    public function truncateTable()
    {
        $tables = [
            $this->resource->getTableName(self::AGGREGATION_DAILY),
            $this->resource->getTableName(self::AGGREGATION_MONTHLY),
            $this->resource->getTableName(self::AGGREGATION_YEARLY),
        ];
        $connection = $this->resource->getConnection();

        foreach ($tables as $table) {
            $connection->truncateTable($table);
        }
    }

    /**
     * @param $connection
     * @param $type
     * @param $column
     * @param $mainTable
     * @param $aggregationTable
     * @return $this
     */
    public function updateReportMonthlyYearly($connection, $type, $column, $mainTable, $aggregationTable)
    {
        $periodSubSelect = $connection->select();
        $ratingSubSelect = $connection->select();
        $ratingSelect = $connection->select();

        switch ($type) {
            case 'year':
                $periodCol = $connection->getDateFormatSql('t.period', '%Y-01-01');
                break;
            case 'month':
                $periodCol = $connection->getDateFormatSql('t.period', '%Y-%m-01');
                break;
            default:
                $periodCol = 't.period';
                break;
        }

        $columns = [
            'period' => 't.period',
            'store_id' => 't.store_id',
            'product_id' => 't.product_id',
            'product_sku' => 't.product_sku',
            'product_name' => 't.product_name',
        ];

        if ($type === 'day') {
            $columns['id'] = 't.id';  // to speed-up insert on duplicate key update
        }

        $cols = array_keys($columns);
        $cols['total_qty'] = new Zend_Db_Expr('SUM(t.' . $column . ')');
        $periodSubSelect->from(
            ['t' => $mainTable],
            $cols
        )->group(
            ['t.store_id', $periodCol, 't.product_id']
        )->order(
            ['t.store_id', $periodCol, 'total_qty DESC']
        );

        $cols = $columns;
        $cols[$column] = 't.total_qty';

        $cols['prevStoreId'] = new Zend_Db_Expr('(@prevStoreId := t.`store_id`)');
        $cols['prevPeriod'] = new Zend_Db_Expr("(@prevPeriod := {$periodCol})");
        $ratingSubSelect->from($periodSubSelect, $cols);

        $cols = $columns;
        $cols['period'] = $periodCol;
        $cols[$column] = 't.' . $column;

        $ratingSelect->from($ratingSubSelect, $cols);

        $sql = $ratingSelect->insertFromSelect($aggregationTable, array_keys($cols));
        $connection->query("SET @pos = 0, @prevStoreId = -1, @prevPeriod = '0000-00-00'");
        $connection->query($sql);
        return $this;
    }
}
