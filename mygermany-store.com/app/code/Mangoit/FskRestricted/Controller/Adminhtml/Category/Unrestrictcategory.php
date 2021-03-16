<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_FskRestricted
 * @author    Mangoit
 */
namespace Mangoit\FskRestricted\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Mangoit Marketplace admin seller controller
 */
class Unrestrictcategory extends Action
{
    /**
     * @param Action\Context $context
     */
    protected $newObjectManager;

    public function __construct(
        Context $context,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->newObjectManager = $objectmanager;
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $parameters = $this->getRequest()->getParams();
        $categoryModel = $this->newObjectManager->create('Mangoit\FskRestricted\Model\Restrictedcategory');
        $productModel = $this->newObjectManager->create('Mangoit\FskRestricted\Model\Restrictedproduct');
        unset($parameters['key']);
        unset($parameters['isAjax']);
        unset($parameters['form_key']);
        try {
            foreach ($parameters['catIdArray'] as $value) {
                $categoryModel->load($value, 'category_id');
                $categoryModel->setRestrictedCountries();
                $categoryModel->save();
                $categoryModel->unsetData();

                $productModelCollection = $productModel->getCollection();
                $productModelCollection->addFieldToFilter('category_id', array('eq' => $value));
                foreach ($productModelCollection as $item) {
                   $productModel->load($item->getId());
                   $productModel->delete();
                   $productModel->unsetData();
                }
                $productModelCollection->clear()->getSelect()->reset('where');
            }  
            $this->messageManager->addSuccess( __('Removed country restriction from selected categories.  Please reindex Solr in order to apply your changes.'));
            echo "true";          
        } catch (Exception $e) {
            $this->messageManager->addError( __('Something went wrong.'));
            echo "false";
        }
        
    }
}   