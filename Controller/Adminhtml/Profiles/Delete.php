<?php

declare(strict_types=1);

namespace PayPal\Subscription\Controller\Adminhtml\Profiles;

use Exception;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use PayPal\Subscription\Model\FrequencyProfileFactory;
use PayPal\Subscription\Model\ResourceModel\FrequencyProfile as FrequencyProfileResource;

/**
 * Controller for deleting Frequency Profiles
 */
class Delete extends Action
{
    public const ADMIN_RESOURCE = 'PayPal_Subscription::subscription_frequency_profiles_delete';

    /**
     * @var FrequencyProfileFactory
     */
    private $frequencyProfile;

    /**
     * @var FrequencyProfileResource
     */
    private $frequencyProfileResource;

    /**
     * Delete constructor.
     *
     * @param Action\Context $context
     * @param FrequencyProfileFactory $frequencyProfile
     * @param FrequencyProfileResource $frequencyProfileResource
     */
    public function __construct(
        Action\Context $context,
        FrequencyProfileFactory $frequencyProfile,
        FrequencyProfileResource $frequencyProfileResource
    ) {
        parent::__construct($context);
        $this->frequencyProfile = $frequencyProfile;
        $this->frequencyProfileResource = $frequencyProfileResource;
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        $frequencyProfile = $this->frequencyProfile->create();
        $this->frequencyProfileResource->load($frequencyProfile, $id);

        $frequencyProfileName = $frequencyProfile->getName();

        try {
            $this->frequencyProfileResource->delete($frequencyProfile);
            $this->messageManager->addSuccessMessage(
                __('Frequency Option "%1" has been deleted successfully', $frequencyProfileName)
            );
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->_redirect('*/*/index');
    }

    /**
     * @return bool
     */
    public function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
