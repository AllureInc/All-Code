<?php

namespace Mangoit\Advertisement\Controller\Adminhtml\Saveblock;
/**
* 
*/
class Uploadimg extends \Magento\Backend\App\Action
{
	
	protected $_objectManager;

	public function __construct(
        \Magento\Backend\App\Action\Context $context,
		\Magento\Framework\ObjectManagerInterface $objectmanager
    ){
		$this->_objectManager = $objectmanager;
        parent::__construct($context);
    }

    public function execute()
    {
    	$parameters = $this->getRequest()->getParams();
        $files = $this->getRequest()->getFiles();
     //    // $model = $this->_objectManager->create('Webkul\MpAdvertisementManager\Model\Pricing');
     //    $blockPos = $parameters['blockId'];
     //    $adType = $parameters['user'];
     //    $contentType = $parameters['content'];
     //    $session = $this->_objectManager->create('Magento\Framework\Session\SessionManagerInterface');
     //    $data = ['block'=> $blockPos,'content_type'=> $contentType, 'ad_type'=> $adType];
     //    // $session->start();
     //    $session->unsAdvertise();
     //    $session->setAdvertise($data);
     //    // $block->setData($data);
     //    // $block = $model->load($parameters['blockId'], 'block_position');
     //    // $block->setContentType($contentType);
     //    // $block->setAdType($adType);
     //    // $block->save();
    	// echo "<pre>";

    	// print_r($block->getData());
    	print_r($files);
    }
}
