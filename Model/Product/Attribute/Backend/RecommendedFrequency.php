<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model\Product\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\Serialize\SerializerInterface;
use PayPal\Subscription\Setup\Patch\Data\InstallRecommendedFrequencyAttributes;
use PayPal\Subscription\Api\Data\FrequencyProfileInterface;
use PayPal\Subscription\Api\FrequencyProfileRepositoryInterface;
use PayPal\Subscription\Setup\Patch\Data\AddProductSubscriptionAttributes;

class RecommendedFrequency extends AbstractBackend
{
    /**
     * @var FrequencyProfileRepositoryInterface
     */
    private $frequencyProfileRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * RecommendedFrequency constructor
     *
     * @param FrequencyProfileRepositoryInterface $frequencyProfileRepository
     * @param SerializerInterface $serializer
     */
    public function __construct(
        FrequencyProfileRepositoryInterface $frequencyProfileRepository,
        SerializerInterface $serializer
    ) {
        $this->frequencyProfileRepository = $frequencyProfileRepository;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function beforeSave($object)
    {
        $recommendedFrequency = $object->getData(
            InstallRecommendedFrequencyAttributes::RECOMMENDED_FREQUENCY_ATTRIBUTE_CODE
        ) ?: null;
        if ($recommendedFrequency === null) {
            return parent::beforeSave($object);
        }
        $frequencyProfile = $object->getData(
            AddProductSubscriptionAttributes::SUBSCRIPTION_FREQUENCY_PROFILE
        ) ?: null;
        if (!$frequencyProfile) {
            $object->setData(
                InstallRecommendedFrequencyAttributes::RECOMMENDED_FREQUENCY_ATTRIBUTE_CODE,
                null
            );
            return parent::beforeSave($object);
        }
        /** @var FrequencyProfileInterface $frequencyProfile */
        $frequencyProfile = $this->frequencyProfileRepository->getById(
            (int) $frequencyProfile
        );
        $frequencyProfileOptions = $this->serializer->unserialize(
            $frequencyProfile->getFrequencyOptions()
        );
        $frequencyProfileOptionIds = [];
        foreach ($frequencyProfileOptions as $frequencyProfileOption) {
            $frequencyProfileOptionIds[] = $frequencyProfileOption['interval'];
        }
        if (!in_array(
            $recommendedFrequency,
            $frequencyProfileOptionIds
        )) {
            $object->setData(
                InstallRecommendedFrequencyAttributes::RECOMMENDED_FREQUENCY_ATTRIBUTE_CODE,
                null
            );
        }
        return parent::beforeSave($object);
    }
}
