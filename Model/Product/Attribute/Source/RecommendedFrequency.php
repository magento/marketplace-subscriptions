<?php
declare(strict_types=1);

namespace PayPal\Subscription\Model\Product\Attribute\Source;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use PayPal\Subscription\Api\Data\FrequencyProfileInterface;
use PayPal\Subscription\Api\FrequencyProfileRepositoryInterface;
use PayPal\Subscription\Registry\CurrentProduct;
use PayPal\Subscription\Setup\Patch\Data\AddProductSubscriptionAttributes;

class RecommendedFrequency extends AbstractSource
{
    /**
     * @var CurrentProduct
     */
    private $currentProductRegistry;

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
     * @param CurrentProduct $currentProductRegistry
     * @param FrequencyProfileRepositoryInterface $frequencyProfileRepository
     * @param SerializerInterface $serializer
     */
    public function __construct(
        CurrentProduct $currentProductRegistry,
        FrequencyProfileRepositoryInterface $frequencyProfileRepository,
        SerializerInterface $serializer
    ) {
        $this->currentProductRegistry = $currentProductRegistry;
        $this->frequencyProfileRepository = $frequencyProfileRepository;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function getAllOptions(): array
    {
        $this->_options = [];
        /** @var ProductInterface|Product $currentProduct */
        $currentProduct = $this->currentProductRegistry->get();
        $productFrequencyProfileId = $currentProduct->getData(
            AddProductSubscriptionAttributes::SUBSCRIPTION_FREQUENCY_PROFILE
        ) ?: null;
        if (!$productFrequencyProfileId) {
            return $this->getNoFrequencyOptions();
        }
        try {
            /** @var FrequencyProfileInterface $productFrequencyProfile */
            $productFrequencyProfile = $this->frequencyProfileRepository->getById(
                (int) $productFrequencyProfileId
            );
            $profileOptions = $this->serializer->unserialize(
                $productFrequencyProfile->getFrequencyOptions()
            );
            $this->_options[] = [
                'label' => __('Please Select'),
                'value' => ''
            ];
            foreach ($profileOptions as $profileOption) {
                $this->_options[] = [
                    'label' => $profileOption['name'],
                    'value' => $profileOption['interval']
                ];
            }
            return $this->_options;
        } catch (NoSuchEntityException $e) {
            return $this->getNoFrequencyOptions();
        }
    }

    /**
     * Return options used if we can't determine frequency profile
     *
     * @return array[]
     */
    private function getNoFrequencyOptions(): array
    {
        return [
            [
                'label' => __('Please select a Frequency Profile'),
                'value' => ''
            ]
        ];
    }
}
