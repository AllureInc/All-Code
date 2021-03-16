<?php
/**
 * Module: Cor_Productmanagement
 * Backend Attribute Model
 * Associate artist catgories with the events
 */
namespace Cor\Productmanagement\Model\Attribute\Source;

class Artistcategory extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Get all options
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $helper = $objectManager->create('Cor\Productmanagement\Helper\Data');
            $artistcategories = $helper->getArtistCategoriesList();
            $options[] = ['label' => __('Select Artist Category'), 'value' => ''];
            foreach ($artistcategories as $artistcategory) {
                if ($artistcategory['status'] == 1) {
                    $options[] = ['label' => __($artistcategory['category_name']), 'value' => $artistcategory['id']];
                }
            }
            $this->_options = $options;
        }        
        return $this->_options;
    }
}