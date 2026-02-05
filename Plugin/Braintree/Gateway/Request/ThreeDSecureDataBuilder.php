<?php
declare(strict_types=1);

namespace PayPal\Subscription\Plugin\Braintree\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use PayPal\Braintree\Gateway\Helper\SubjectReader;
use PayPal\Braintree\Gateway\Request\ThreeDSecureDataBuilder as Subject;
use PayPal\Subscription\Gateway\Data\Order\OrderAdapter;

class ThreeDSecureDataBuilder
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Intercept after build method and return empty array for 3DS Request Data to disable for releases
     *
     * @param Subject $subject
     * @param array $result
     * @param array $buildSubject
     * @return array|string[]
     */
    public function afterBuild(
        Subject $subject,
        array $result,
        array $buildSubject
    ): array|string {
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        /** @var OrderAdapter $paymentOrder */
        $paymentOrder = $paymentDO->getOrder();
        if ($paymentOrder->getIsSubscriptionRelease() === true) {
            return [];
        }
        return $result;
    }
}
