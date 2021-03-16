<?php
/**
 * Module: Cor_Productmanagement
 * Backend Attribute Model
 * Associate artists with the events
 */
namespace Cor\Productmanagement\Model\Attribute\Source;

class Artist extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
            $artists = $helper->getArtistsList();
            $options[] = ['label' => __('Select Artist'), 'value' => ''];
            foreach ($artists as $artist) {
                $options[] = ['label' => __($artist['artist_name']), 'value' => $artist['id']];
            }
            $this->_options = $options;
        }
        return $this->_options;
    }
}