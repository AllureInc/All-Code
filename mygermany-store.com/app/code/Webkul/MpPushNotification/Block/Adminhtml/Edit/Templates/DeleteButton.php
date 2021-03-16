<?php
/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpPushNotification\Block\Adminhtml\Edit\Templates;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Webkul\MpPushNotification\Block\Adminhtml\Edit\GenericButton;

/**
 * Class DeleteButton.
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $templateId = $this->getTemplateId();
        $data = [];
        if ($templateId) {
            $data = [
                'label' => __('Delete Template'),
                'class' => 'delete',
                'id' => 'banner-edit-delete-button',
                'data_attribute' => [
                    'url' => $this->getDeleteUrl(),
                ],
                'on_click' => '',
                'sort_order' => 20,
            ];
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', ['id' => $this->getTemplateId()]);
    }
}
