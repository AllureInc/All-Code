<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\Gdpr\Controller\Adminhtml\Newsletter;

/**
 * Class DeletePersonalData
 * @package Scommerce\Gdpr\Controller\Adminhtml\Newsletter
 */
class DeletePersonalData extends \Magento\Newsletter\Controller\Adminhtml\Subscriber
{
    const ADMIN_RESOURCE = 'Scommerce_Gdpr::config';

    /** @var \Scommerce\Gdpr\Model\Service\Account */
    private $account;

    /** @var \Magento\Framework\App\Response\Http\FileFactory */
    private $fileFactory;

    /** @var \Magento\Customer\Api\CustomerRepositoryInterface */
    private $customerRepository;

    /** @var \Scommerce\Gdpr\Helper\Data */
    private $helper;

    /**
     * DeletePersonalData constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Scommerce\Gdpr\Model\Service\Account $account
     * @param \Scommerce\Gdpr\Helper\Data $helper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Scommerce\Gdpr\Model\Service\Account $account,
        \Scommerce\Gdpr\Helper\Data $helper
    ) {
        parent::__construct($context, $fileFactory);
        $this->fileFactory = $fileFactory;
        $this->customerRepository = $customerRepository;
        $this->account = $account;
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function execute()
    {
        if (! $this->helper->isEnabled()) {
            return $this->_redirect('admin/dashboard/index');
        }

        $subscribersIds = $this->getRequest()->getParam('subscriber');
        if (!is_array($subscribersIds)) {
            $subscribersIds = explode(',', $subscribersIds);
        }

        if (!is_array($subscribersIds) || count($subscribersIds) == 0) {
            $this->messageManager->addError(__('Please select one or more subscribers.'));
            return $this->_redirect('newsletter/subscriber/index');
        }

        foreach ($subscribersIds as $subscriberId) {
            /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
            $subscriber = $this->_objectManager->create(
                \Magento\Newsletter\Model\Subscriber::class
            )->load(
                $subscriberId
            );
            $customerId = $subscriber->getCustomerId();
            if ($customerId) {
                $customer = $this->customerRepository->getById($customerId);
                if ($customer != null) {
                    $this->account->anonymize($customer);
                }
            } else {
                $this->account->anonymize(null, $subscriber->getEmail());
            }
        }

        return $this->_redirect('newsletter/subscriber/index');
    }
}
