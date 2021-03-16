<?php
/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpPushNotification\Controller\Adminhtml\ManageUsers;

use Webkul\MpPushNotification\Controller\Adminhtml\ManageUsers;
use Webkul\MpPushNotification\Model\UsersToken;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\ResultFactory;

class MassDelete extends ManageUsers
{
    /**
     * Massactions filter.
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var UsersToken
     */
    protected $_usersToken;

    /**
     * [__construct description]
     * @param \Magento\Backend\App\Action\Context $context
     * @param Filter                              $filter
     * @param UsersToken                          $usersToken
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Filter $filter,
        UsersToken $usersToken
    ) {
        $this->filter = $filter;
        $this->_usersToken = $usersToken;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {

        $collection = $this->filter->getCollection($this->_usersToken->getCollection());
        $tokenDeleted = 0;
        foreach ($collection->getItems() as $token) {
            $token->delete();
            ++$tokenDeleted;
        }
        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been deleted.', $tokenDeleted)
        );

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }
}
