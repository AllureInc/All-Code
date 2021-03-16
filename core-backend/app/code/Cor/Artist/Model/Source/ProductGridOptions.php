<?php

/**
 * ProductGridFilterOptions type model
 */
namespace Cor\Artist\Model\Source;

use Magento\Catalog\Model\Product;
use Magento\Framework\Data\OptionSourceInterface;

class ProductGridOptions implements OptionSourceInterface
{
    /**
     * @var Cor\Artist\Model\Artist
     */
    protected $_artistFactory;

    /**
     * Construct
     *
     * @param Cor\Artist\Model\Artist $artistFactory
     */
    public function __construct(
        \Cor\Artist\Model\Artist $artistFactory
    ) {
        $this->_artistFactory = $artistFactory;
    }

    /**
     * @return array
     */
    public function getArtists(){
        return $artists = $this->_artistFactory->getCollection();
    }

    /**
     * @return array
     */
    public function getOptions(){
        $artists = $this->getArtists();
        $options[] = ['value' => '', 'label' => __('-- Select --')];
        if ($artists) {
            foreach ($artists as $artist) {
                $options[] = ['value' => $artist->getId(), 'label' => __($artist->getArtistName())];
            }
        }
        return $options;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }
}
