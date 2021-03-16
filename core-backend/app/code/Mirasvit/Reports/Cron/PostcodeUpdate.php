<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-reports
 * @version   1.3.20
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Reports\Cron;

use Mirasvit\Reports\Model\ResourceModel\Postcode\CollectionFactory as PostcodeCollectionFactory;
use Mirasvit\Reports\Helper\Geo as GeoHelper;

class PostcodeUpdate
{
    /**
     * @var PostcodeCollectionFactory
     */
    protected $postcodeCollectionFactory;

    /**
     * @var GeoHelper
     */
    protected $geoHelper;

    /**
     * @param PostcodeCollectionFactory $postcodeCollectionFactory
     * @param GeoHelper                 $geoHelper
     */
    public function __construct(
        PostcodeCollectionFactory $postcodeCollectionFactory,
        GeoHelper $geoHelper
    ) {
        $this->postcodeCollectionFactory = $postcodeCollectionFactory;
        $this->geoHelper = $geoHelper;
    }

    /**
     * @param bool $verbose
     *
     * @return void
     */
    public function execute($verbose = false)
    {
        $this->batchUpdate($verbose);
        $this->batchMerge($verbose);
    }

    /**
     * {@inheritdoc}
     */
    public function batchUpdate($verbose = false)
    {
        $limit = 100;

        $lastId = 0;

        do {
            $collection = $this->postcodeCollectionFactory->create()
                ->addFieldToFilter('postcode_id', ['gt' => $lastId])
                ->setOrder('postcode_id', 'asc');

            $collection->getSelect()
                ->where('(original NOT LIKE "%google%" OR original IS NULL)')
                ->where('country_id <> "GB"')
                ->where('updated = 0');

            $collection->setPageSize(10);

            $collection->load();

            $toUpdate = [];
            /** @var \Mirasvit\Reports\Model\Postcode $row */
            foreach ($collection as $row) {
                $toUpdate[$row->getId()] = $row->getCountryId() . ': ' . $row->getPostcode();
                $lastId = $row->getId();
            }

            $resultsGoogle = $this->geoHelper->findInGoogle($toUpdate);

            foreach ($resultsGoogle as $id => $rows) {
                /** @var \Mirasvit\Reports\Model\Postcode $model */
                $model = $collection->getItemById($id);

                $original = $model->getData('original');
                $original = json_decode($original, true);
                $original['google'] = $rows;

                $model->setOriginal(json_encode($original))
                    ->save();

                --$limit;

                if ($verbose) {
                    echo $model . PHP_EOL;
                }
            }
        } while ($collection->count() > 0 && $limit > 0);
    }

    /**
     * {@inheritdoc}
     */
    public function batchMerge($verbose = false)
    {
        $lastId = 0;

        do {
            $collection = $this->postcodeCollectionFactory->create()
                ->addFieldToFilter('postcode_id', ['gt' => $lastId])
                ->setOrder('postcode_id', 'asc');
            $collection->getSelect()
                ->where('original LIKE "%google%"')
                ->where('updated = 0');

            $collection->setPageSize(100);

            $collection->load();

            /** @var \Mirasvit\Reports\Model\Postcode $model */
            foreach ($collection as $model) {
                $data = json_decode($model->getOriginal(), true);
                if (!isset($data['google'][0])) {
                    $lastId = $model->getId();
                    continue;
                }

                $google = [
                    'state'     => false,
                    'province'  => false,
                    'place'     => false,
                    'community' => false,
                    'lat'       => false,
                    'lng'       => false,
                ];

                foreach ($data['google'][0]['address_components'] as $component) {
                    if ($component['types'][0] == 'locality') {
                        $google['place'] = $component['long_name'];
                    }
                    if ($component['types'][0] == 'administrative_area_level_1') {
                        $google['state'] = $component['long_name'];
                    }
                    if ($component['types'][0] == 'administrative_area_level_2') {
                        $google['province'] = $component['long_name'];
                    }
                }

                $google['lat'] = $data['google'][0]['geometry']['location']['lat'];
                $google['lng'] = $data['google'][0]['geometry']['location']['lng'];

                $result = $google;

                $model->setState($result['state'])
                    ->setProvince($result['province'])
                    ->setPlace($result['place'])
                    ->setCommunity($result['community'])
                    ->setLat($result['lat'])
                    ->setLng($result['lng'])
                    ->setUpdated(1)
                    ->save();

                if ($verbose) {
                    echo $model . PHP_EOL;
                }

                $lastId = $model->getId();
            }
        } while ($collection->count() > 0);
    }
}