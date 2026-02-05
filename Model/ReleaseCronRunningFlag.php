<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Flag;

class ReleaseCronRunningFlag extends Flag implements ReleaseCronRunningFlagInterface
{
    /**
     * @var string
     */
    protected $_flagCode = 'subscriptions.release.cron.running';

    private const IS_RUNNING_KEY = 'is_running';

    /**
     * Return boolean on whether subscription release cron is running
     *
     * @return bool
     * @throws LocalizedException
     */
    public function getIsReleaseCronRunning(): bool
    {
        $releaseCronRunningFlagObject = $this->loadSelf();
        $flagData = $releaseCronRunningFlagObject->getFlagData();
        $flagData = is_array($flagData) ? $flagData : [];
        $isRunning = $flagData[self::IS_RUNNING_KEY] ?? false;
        return (bool) $isRunning;
    }

    /**
     * Set flag data to boolean on whether release cron is running or not
     *
     * @param bool $isRunning
     * @throws LocalizedException
     */
    public function setIsReleaseCronRunning(bool $isRunning): void
    {
        $releaseCronRunningFlagObject = $this->loadSelf();
        $releaseCronRunningFlagObject->setFlagData([
            self::IS_RUNNING_KEY => $isRunning
        ]);
        $releaseCronRunningFlagObject->save();
    }
}
