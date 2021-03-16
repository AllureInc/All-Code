<?php
/**
 * Created by PhpStorm.
 * User: mageinn
 * Date: 14.08.2018
 * Time: 15:46
 */

namespace Scommerce\Gdpr\Controller\Adminhtml\Customer;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;

/**
 * Class DeletePersonalData
 * @package Scommerce\Gdpr\Controller\Adminhtml\Customer
 */
class DeletePersonalData extends \Magento\Customer\Controller\Adminhtml\Index\AbstractMassAction
{
    const ADMIN_RESOURCE = 'Scommerce_Gdpr::config';

    /** @var \Scommerce\Gdpr\Model\Service\Account */
    private $account;

    /** @var \Magento\Customer\Api\CustomerRepositoryInterface */
    private $customerRepository;

    /** @var \Scommerce\Gdpr\Helper\Data */
    private $helper;

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
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Scommerce\Gdpr\Model\Service\Account $account,
        \Scommerce\Gdpr\Helper\Data $helper
    ) {
        parent::__construct($context, $filter, $collectionFactory);
        $this->customerRepository = $customerRepository;
        $this->account = $account;
        $this->helper = $helper;
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

        foreach ($collection->getAllIds() as $customerId) {
            $customer = $this->customerRepository->getById($customerId);
            if ($customer != null) {
                $this->account->anonymize($customer);
            }
        }

        return $this->_redirect('customer/index/index');
    }
}
