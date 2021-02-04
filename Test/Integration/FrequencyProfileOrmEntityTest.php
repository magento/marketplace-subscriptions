<?php

declare(strict_types=1);

namespace PayPal\Subscription\Test\Integration;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\TestFramework\ObjectManager;
use PayPal\Subscription\Model\FrequencyProfile;
use PayPal\Subscription\Model\ResourceModel\FrequencyProfile as FrequencyResource;
use PayPal\Subscription\Model\ResourceModel\FrequencyProfile\Collection;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PayPal\Subscription\Model\FrequencyProfile
 * @covers \PayPal\Subscription\Model\ResourceModel\FrequencyProfile
 * @magentoDbIsolation enabled
 */
class FrequencyProfileOrmEntityTest extends TestCase
{
    /**
     * @return FrequencyProfile
     */
    public function instantiateFrequencyProfile(): FrequencyProfile
    {
        return ObjectManager::getInstance()->create(FrequencyProfile::class);
    }

    /**
     * @return FrequencyResource
     */
    public function instantiateResourceModel(): FrequencyResource
    {
        return ObjectManager::getInstance()->create(FrequencyResource::class);
    }

    /**
     * @return FrequencyProfile
     * @throws AlreadyExistsException
     */
    public function createFrequencyProfile(): FrequencyProfile
    {
        $frequencyProfile = $this->instantiateFrequencyProfile();
        $frequencyProfile->setName(uniqid('Profile-', true));
        $frequencyProfile->setFrequencyOptions(json_encode(['foo' => 'bar']));
        $this->instantiateResourceModel()->save($frequencyProfile);
        return $frequencyProfile;
    }

    /**
     * @throws AlreadyExistsException
     */
    public function testCanSaveAndLoad(): void
    {
        $frequencyProfile = $this->createFrequencyProfile();

        $frequencyProfileToLoad = $this->instantiateFrequencyProfile();
        $this->instantiateResourceModel()->load($frequencyProfileToLoad, $frequencyProfile->getId());

        $this->assertSame($frequencyProfile->getId(), $frequencyProfileToLoad->getId());
        $this->assertSame($frequencyProfile->getName(), $frequencyProfileToLoad->getName());
        $this->assertSame($frequencyProfile->getFrequencyOptions(), $frequencyProfileToLoad->getFrequencyOptions());
    }

    /**
     * @throws AlreadyExistsException
     */
    public function testCanLoadMultipleFrequencyProfiles(): void
    {
        $frequencyProfileA = $this->createFrequencyProfile();
        $frequencyProfileB = $this->createFrequencyProfile();

        /** @var Collection $collection */
        $collection = ObjectManager::getInstance()->create(Collection::class);
        $this->assertContains($frequencyProfileA->getId(), array_keys($collection->getItems()));
        $this->assertContains($frequencyProfileB->getId(), array_keys($collection->getItems()));
    }
}
