<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */


namespace Amasty\SeoToolKit\Plugin\Model\Menu;

use Magento\Backend\Model\Menu;

class Builder
{
    protected $seoLinks = [
        'Amasty_Meta::seotoolkit',
        'Amasty_XmlSitemap::xml_sitemap'
    ];

    public function afterGetResult($subject, Menu $menu)
    {
        foreach ($this->seoLinks as $link) {
            $item = $menu->get($link);
            if ($item) {
                foreach ($item->getChildren() as $sort => $childItem) {
                    $menu->move($childItem->getId(), 'Amasty_SeoToolKit::seotoolkit', $sort * 100);
                }

                $menu->remove($link);
            }

        }

        return $menu;
    }
}
