<?php

namespace Cor\Pos\Model\Artist;

use Cor\Pos\Api\Artist\ArtistInterface;

/**
 * Class Artist
 */
class Artist implements ArtistInterface
{
    /**
     * @var artistFactory
    */
    protected $_artistFactory;

    /**
     * @param Cor\Artist\Model\ArtistFactory $artistFactory
     */
    public function __construct(
        \Cor\Artist\Model\ArtistFactory $artistFactory
    ) {
        $this->_artistFactory = $artistFactory;
    }

    /**
     * List all artists
     * @api
     * @return string
     */
    public function getList() {

        $model = $this->_artistFactory->create();
        $collection = $model->getCollection();
        $artistsData = array();

        foreach ($collection->getData() as $data) {
            $artistsData[] = array(
                'id'=> $data['artist_id'],
                'artist_name'=> $data['artist_name'],
                'artist_rep_name'=> $data['artist_rep_name'],
                'artist_rep_number'=> $data['artist_rep_number'],
                'artist_rep_email'=> $data['artist_rep_email'],
                'artist_tax_id'=> $data['artist_tax_id'],
                'wnine_received'=> $data['wnine_received'],
            );
        }

        $artists = array('artists' => $artistsData);
        echo json_encode($artists);
        exit;
    }
}
