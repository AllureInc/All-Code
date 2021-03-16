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

class Massdelete extends CreditrulesController
{
    /**
     * @var Webkul\Walletsystem\Api\WalletCreditRepositoryInterface
     */
    private $_creditRuleRepository;
    /**
     * @var Filter
     */
    private $_filter;
    /**
     * @var Walletsystem\Model\ResourceModel\Walletcreditrules\CollectionFactory
     */
    private $_collectionFactory;

    /**
     * @param Action\Context                                                        $context
     * @param Filter                                                                $filter
     * @param Walletsystem\Api\WalletCreditRepositoryInterface                      $creditRuleRepository
     * @param Walletsystem\Model\ResourceModel\Walletcreditrules\CollectionFactory  $collectionFactory
     */
    public function __construct(
        Action\Context $context,
        Filter $filter,
        Walletsystem\Api\WalletCreditRepositoryInterface $creditRuleRepository,
        Walletsystem\Model\ResourceModel\Walletcreditrules\CollectionFactory $collectionFactory
    ) {
        $this->_creditRuleRepository = $creditRuleRepository;
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $creditRuleDeleted = 0;
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());
        try {
            foreach ($collection as $item) {
                $this->_creditRuleRepository->deleteById($item->getEntityId());
                $creditRuleDeleted++;
            }
            $this->messageManager->addSuccess(
                __('A total of %1 record(s) have been deleted.', $creditRuleDeleted)
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while Deleting the data.')
            );
        }
        return $resultRedirect->setPath('*/*/creditrules');
    }
}
