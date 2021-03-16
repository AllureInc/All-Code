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

namespace Webkul\Walletsystem\Controller\Adminhtml\Transfer;

use Webkul\Walletsystem\Controller\Adminhtml\Transfer as TransferController;
use Magento\Backend\App\Action;
use Webkul\Walletsystem;
use Magento\Ui\Component\MassAction\Filter;

class Payeedelete extends TransferController
{
    /**
     * @var Filter
     */
    private $_filter;
    /**
     * @var Walletsystem\Model\ResourceModel\WalletPayee\CollectionFactory
     */
    private $_collectionFactory;

    /**
     * @param Action\Context                                                        $context
     * @param Filter                                                                $filter
     * @param Walletsystem\Model\ResourceModel\WalletPayee\CollectionFactory  $collectionFactory
     */
    public function __construct(
        Action\Context $context,
        Filter $filter,
        Walletsystem\Model\ResourceModel\WalletPayee\CollectionFactory $collectionFactory
    ) {
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
        $payeedeleted = 0;
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());
        try {
            foreach ($collection as $item) {
                $item->delete();
                $payeedeleted++;
            }
            $this->messageManager->addSuccess(
                __('A total of %1 record(s) have been deleted.', $payeedeleted)
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
        return $resultRedirect->setPath('*/*/payeelist');
    }
}
