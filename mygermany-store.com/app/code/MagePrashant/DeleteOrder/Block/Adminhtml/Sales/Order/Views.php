<?php 
namespace MagePrashant\DeleteOrder\Block\Adminhtml\Sales\Order;

use Magento\Sales\Block\Adminhtml\Order\View;

class Views extends View
{
    public function beforeSetLayout(View $view)
    {
		if($this->getHelper()->isEnabled()){
			$view->addButton(
				'button_id',
				[
					'label'     =>  __('Delete Order'),
					'class'     =>  'go',
					'onclick'   =>  "confirmSetLocation('Are you sure you want to do this?', '{$this->getDeleteUrl()}');"
				]
			);
		}
    }

    public function getDeleteUrl()
    {
        return $this->getUrl('deleteorder/order/index', ['_current'=>true]);
    }
	
	public function getHelper(){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		return $objectManager->get('MagePrashant\DeleteOrder\Helper\Data');
	}
}
