<?php

declare(strict_types=1);

namespace PayPal\Subscription\Controller\Adminhtml\Profiles;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use PayPal\Subscription\Api\Data\FrequencyProfileInterfaceFactory;
use PayPal\Subscription\Api\FrequencyProfileRepositoryInterface;
use PayPal\Subscription\Model\FrequencyProfileFactory;

/**
 * Controller for saving Frequency Profiles
 */
class Save extends Action
{
    /**
     * @var FrequencyProfileFactory
     */
    private $frequencyProfile;

    /**
     * @var FrequencyProfileRepositoryInterface
     */
    private $frequencyProfileRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Save constructor.
     *
     * @param Action\Context $context
     * @param FrequencyProfileInterfaceFactory $frequencyProfile
     * @param FrequencyProfileRepositoryInterface $frequencyProfileRepository
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Action\Context $context,
        FrequencyProfileInterfaceFactory $frequencyProfile,
        FrequencyProfileRepositoryInterface $frequencyProfileRepository,
        SerializerInterface $serializer
    ) {
        parent::__construct($context);
        $this->frequencyProfile = $frequencyProfile;
        $this->frequencyProfileRepository = $frequencyProfileRepository;
        $this->serializer = $serializer;
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        $id = (int) $data['id'];

        try {
            if ($id > 0) {
                $frequencyProfile = $this->frequencyProfileRepository->getById($id);
            } else {
                $frequencyProfile = $this->frequencyProfile->create();
            }

            /**
             * Filter to remove empty post values
             */
            $data = array_filter($data, static function ($value) {
                return $value !== '';
            });

            $data['frequency_options'] = $this->serializer->serialize($data['frequency_options']);

            $frequencyProfile->setData($data);

            $this->frequencyProfileRepository->save($frequencyProfile);

            $this->messageManager->addSuccessMessage(
                __('Frequency Option "%1" has been saved successfully', $data['name'])
            );
        } catch (NoSuchEntityException | CouldNotSaveException $e) {
            $this->messageManager->addErrorMessage(
                __('Unable to save this Profile at this time. %1', $e->getMessage())
            );
        }

        $this->_redirect('*/*/index');
    }
}
