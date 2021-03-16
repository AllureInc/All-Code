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

namespace Webkul\Walletsystem\Controller\Adminhtml\Wallet;

use Webkul\Walletsystem\Controller\Adminhtml\Wallet as WalletController;
use Magento\Backend\App\Action;
use Webkul\Walletsystem;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\Walletsystem\Model\Wallettransaction;

class MassupdateBanktransfer extends WalletController
{
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
     * @param Walletsyste\Api\WalletTransactionRepositoryInterface                 $creditRuleRepository
     * @param Walletsyste\Model\ResourceModel\Wallettransaction\CollectionFactory $collectionFactory
     */
    public function __construct(
        Action\Context $context,
        \Webkul\Walletsystem\Helper\Mail $mailHelper,
        Filter $filter,
        Walletsystem\Model\ResourceModel\Wallettransaction\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->_filter = $filter;
        $this->_mailHelper = $mailHelper;
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
            
            if (isset($data['selected'])) {
                $selected = count($data['selected']);
            } else {
                $selected = __("All Selected");
            }
            
            $status = Wallettransaction::WALLET_TRANS_STATE_APPROVE;
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
                /**
                 * send mail to all approved transactions
                 * @param wallet transfer collection$collection
                 **/
                $this->_mailHelper->sendCustomerBulkTransferApproveMail($collection);
                $this->messageManager->addSuccess(
                    __(
                        '%1 record(s) successfully updated.',
                        $selected
                    )
                );
            }

            return $resultRedirect->setPath(
                '*/*/bankdetails',
                ['sender_type'=>Wallettransaction::CUSTOMER_TRANSFER_BANK_TYPE]
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __($e->getMessage())
            );
        }
        return $resultRedirect->setPath(
            '*/*/bankdetails',
            ['sender_type'=>Wallettransaction::CUSTOMER_TRANSFER_BANK_TYPE]
        );
    }
}
