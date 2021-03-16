<?php
namespace Cor\Artistcategory\Controller\Adminhtml\Category;
use Magento\Framework\App\Filesystem\DirectoryList;
/**
* 
*/
class Checkcategory extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $model = $this->_objectManager->create('Cor\Artistcategory\Model\Artistcategory');
        $data = $model->getCollection()->getData();
        $param = $this->getRequest()->getParams();
        foreach ($data as $item) {
            if ($item['category_name'] == $param['category']) {
                if ($param['id'] != $item['id']) {
                    echo "exist";
                    exit();
                }
            }
        }
        echo "not-exist";
    }
}