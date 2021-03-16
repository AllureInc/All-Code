<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action\Context;
use Plenty\Core\Model\ResourceModel\Profile\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Plenty\Core\Api\ProfileRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete
 * @package Plenty\Core\Controller\Adminhtml\Profile
 */
class MassDelete extends AbstractMassAction
{
    /**
     * @var ProfileRepositoryInterface
     */
    protected $_profileRepository;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param ProfileRepositoryInterface $profileRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ProfileRepositoryInterface $profileRepository
    ) {
        parent::__construct($context, $filter, $collectionFactory);
        $this->_profileRepository = $profileRepository;
    }

    /**
     * @param \Magento\Framework\Data\Collection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect|mixed
     */
    protected function massAction(\Magento\Framework\Data\Collection $collection)
    {
        $deleted = 0;
        foreach ($collection->getAllIds() as $profileId) {
            $this->_profileRepository->deleteById($profileId);
            $deleted++;
        }

        if ($deleted) {
            $this->messageManager->addSuccessMessage(__('A total of %s profile(s) were deleted.', $deleted));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }
}