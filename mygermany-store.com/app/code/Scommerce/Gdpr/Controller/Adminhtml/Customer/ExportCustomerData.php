<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\Gdpr\Controller\Adminhtml\Customer;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class ExportCustomerData
 * @package Scommerce\Gdpr\Controller\Adminhtml\Customer
 */
class ExportCustomerData extends \Magento\Customer\Controller\Adminhtml\Index\AbstractMassAction
{
    const ADMIN_RESOURCE = 'Scommerce_Gdpr::config';

    /** @var \Scommerce\Gdpr\Model\Service\Account */
    private $account;

    /** @var \Magento\Framework\App\Response\Http\FileFactory */
    private $fileFactory;

    /** @var \Scommerce\Gdpr\Helper\Data */
    private $helper;

    /** @var \Magento\Customer\Api\CustomerRepositoryInterface */
    private $customerRepository;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Scommerce\Gdpr\Model\Service\Account $account
     * @param \Scommerce\Gdpr\Helper\Data $helper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Scommerce\Gdpr\Model\Service\Account $account,
        \Scommerce\Gdpr\Helper\Data $helper
    ) {
        parent::__construct($context, $filter, $collectionFactory);
        $this->fileFactory = $fileFactory;
        $this->account = $account;
        $this->helper = $helper;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    protected function massAction(AbstractCollection $collection)
    {
        if (! $this->helper->isEnabled()) {
            return $this->_redirect('admin/dashboard/index');
        }
        $file = 'customers_data.csv';
        $content = '';
        foreach ($collection->getAllIds() as $customerId) {
            $customer = $this->customerRepository->getById($customerId);
            if ($customer != null) {
                $content .= $this->account->export($customer) . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;
            }
        }
        return $this->fileFactory->create($file, $content, DirectoryList::VAR_DIR);
    }
}
