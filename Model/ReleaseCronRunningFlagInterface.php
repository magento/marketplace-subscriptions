<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model;

use Magento\Framework\Exception\LocalizedException;

interface ReleaseCronRunningFlagInterface
{
    /**
     * Return Boolean on whether Subscription Release cron is running
     *
     * @return bool
     * @throws LocalizedException
     */
    public function getIsReleaseCronRunning(): bool;

    /**
     * Return Boolean on whether Subscription Release cron is running
     *
     * @param bool $isRunning
     * @return void
     * @throws LocalizedException
     */
    public function setIsReleaseCronRunning(bool $isRunning): void;
}
