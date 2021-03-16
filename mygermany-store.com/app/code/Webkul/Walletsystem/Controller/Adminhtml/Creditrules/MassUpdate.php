<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Controller\Adminhtml\Creditrules;

use Webkul\Walletsystem\Controller\Adminhtml\Creditrules as CreditrulesController;
use Magento\Backend\App\Action;
use Webkul\Walletsystem;
use Magento\Ui\Component\MassAction\Filter;

class MassUpdate extends CreditrulesController
{
    /**
     * @var Webkul\Walletsystem\Api\WalletCreditRepositoryInterface
     */
    protected $_creditRuleRepository;
    /**
     * @var Filter
     */
    protected $_filter;
    /**
     * @var Walletsystem\Model\ResourceModel\Walletcreditrules\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param Action\Context                                           $context
     * @param Filter                                                   $filter
     * @param Walletsyste\Api\WalletCreditRepositoryInterface                 $creditRuleRepository
     * @param Walletsyste\Model\ResourceModel\Walletcreditrules\CollectionFactory $collectionFactory
     */
    public function __construct(
        Action\Context $context,
        Filter $filter,
        Walletsystem\Api\WalletCreditRepositoryInterface $creditRuleRepository,
        Walletsystem\Model\ResourceModel\Walletcreditrules\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->_creditRuleRepository = $creditRuleRepository;
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * Mass Update action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $resultRedirect = $this->resultRedirectFactory->create();
            $data = $this->getRequest()->getParams();
            $status = $data['creditruleupdate'];
            $collection = $this->_filter->getCollection($this->_collectionFactory->create());
            $entityIds = $collection->getAllIds();
            if (count($entityIds)) {
                $coditionArr = [];
                foreach ($entityIds as $key => $id) {
                    $condition = "`entity_id`=".$id;
                    array_push($coditionArr, $condition);
                }
                $coditionData = implode(' OR ', $coditionArr);

                $creditRuleCollection = $this->_collectionFactory->create();
                $creditRuleCollection->setTableRecords(
                    $coditionData,
                    ['status' => $status]
                );

                $this->messageManager->addSuccess(
                    __(
                        'A Total of %1 record(s) successfully updated.',
                        count($entityIds)
                    )
                );
            }
            return $resultRedirect->setPath('*/*/creditrules');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while Updating the data.')
            );
        }
        return $resultRedirect->setPath('*/*/creditrules');
    }
}
