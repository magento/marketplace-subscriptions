<?php
declare(strict_types=1);

namespace PayPal\Subscription\Observer;

use Magento\Framework\DataObject\Copy;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CopyQuoteSubscriptionToOrder implements ObserverInterface
{
    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * CopyQuoteSubscriptionToOrder constructor
     *
     * @param Copy $objectCopyService
     */
    public function __construct(
        Copy $objectCopyService
    ) {
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * Observer before quotee submit to copy fieldset of data
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(
        Observer $observer
    ): self {
        /* @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getData('order');
        /* @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getData('quote');
        if ($quote && $order) {
            $this->objectCopyService->copyFieldsetToTarget(
                'sales_convert_quote',
                'to_order',
                $quote,
                $order
            );
        }
        return $this;
    }
}
