<?php

declare(strict_types=1);

namespace PayPal\Subscription\Plugin;

class NewAddress
{
    public function afterProcessWebsiteMeta($subject, $result)
    {
        return $result;
    }
}
