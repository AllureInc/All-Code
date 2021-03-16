<?php
namespace Mangoit\Fskverified\Controller\Fsk;
/**
* 
*/
use Magento\Framework\App\Action\Action;

class Save extends Action
{
	protected $_objectManager;
	public function __construct(\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Framework\ObjectManagerInterface $objectmanager
		)
	{
		$this->_objectManager = $objectmanager;
		parent::__construct($context);
	}
    
    public function execute()
    {
    	$parameters = $this->getRequest()->getParams();
        $today = date("Y-m-d H:i:s");
    	$userIp = $parameters['ip'];
    	$productId = $parameters['product'];
    	$fskModelData = $this->_objectManager->create('Mangoit\Fskverified\Model\Fskmodel');
    	$collction = $fskModelData->getCollection()->addFieldToFilter('product_id', $productId)->addFieldToFilter('user_ip', $userIp);
        if ($collction->getSize() < 1) {
            $fskModelData->setProductId($productId);
            $fskModelData->setUserIp($userIp);
            $fskModelData->setCreatedAt($today);
            $fskModelData->setUpdatedAt($today);
            $fskModelData->save();
        } else {
            $fskAvailable = 0;
            $rowId = 0;
            $lastVisitDate = 0;
            foreach ($collction->getData() as $key => $value) {
                $rowId = $value['id'];
                $fskAvailable = $value['fsk'];
                $lastVisitDate = $value['updated_at'];

            }
            if ($fskAvailable == '1') {
                $hourdiff = round((strtotime($today) - strtotime($lastVisitDate))/3600, 1);
                if ($hourdiff >= 24) {
                   $setDataCollection = $fskModelData->load($rowId);
                   $setDataCollection->setUpdatedAt($today);
                   $setDataCollection->save();
                }
            }

        }
    }
}