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

class Delete extends CreditrulesController
{
    /**
     * @var Webkul\Walletsystem\Api\WalletCreditRepositoryInterface
     */
    private $_creditRuleRepository;

    /**
     * @param Action\Context                                    $context
     * @param Walletsystem\Api\WalletCreditRepositoryInterface  $creditRuleRepository
     */
    public function __construct(
        Action\Context $context,
        Walletsystem\Api\WalletCreditRepositoryInterface $creditRuleRepository
    ) {
        parent::__construct($context);
        $this->_creditRuleRepository = $creditRuleRepository;
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data && array_key_exists('entity_id', $data)) {
            $entityId = $data['entity_id'];
            if ($entityId) {
                try {
                    $this->_creditRuleRepository->deleteById($entityId);
                    $this->messageManager->addSuccess(
                        __('Credit Rule successfully deleted.')
                    );
                    return $resultRedirect->setPath('*/*/creditrules');
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
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
