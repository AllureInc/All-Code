<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAdvertisementManager\Controller\Adminhtml\Pricing;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;

class ChangeStatus extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Webkul_MpAdvertisementManager::pricing';

    /**
     * @var \Webkul\MpAdvertisementManager\Model\ResourceModel\AdsPurchaseDetail\CollectionFactory
     */
    protected $_adsCollection;

    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @param \Magento\Backend\App\Action\Context                                                    $context
     * @param \Webkul\MpAdvertisementManager\Model\ResourceModel\AdsPurchaseDetail\CollectionFactory $adsCollection
     * @param Filter                                                                                 $filter
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\MpAdvertisementManager\Model\ResourceModel\AdsPurchaseDetail\CollectionFactory $adsCollection,
        Filter $filter
    ) {
    
        parent::__construct($context);
        $this->_adsCollection = $adsCollection;
        $this->_filter = $filter;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $adsIds = $this->_filter->getCollection($this->_adsCollection->create());
        try {
            foreach ($adsIds as $adsId) {
                    $adsId->setEnable($this->getRequest()->getParam('id'))
                    ->save();
            }
            $this->messageManager->addSuccess(__('Total of %1 record(s) were successfully updated', $adsIds->getSize()));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $resultRedirect->setPath('mpadvertisementmanager/pricing/selleradspurchasedetail');
        return $resultRedirect;
    }

    /*
     * Check permission via ACL resource
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
