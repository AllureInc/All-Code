<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            aFcBJOkw7Z741kuBO2iYe07UAZAtDg54mR751Ilexis=
 * Last Modified: 2016-04-14T15:37:35+00:00
 * File:          app/code/Xtento/ProductExport/Model/ResourceModel/Profile/Collection.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\ResourceModel\Profile;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Xtento\ProductExport\Model\Profile', 'Xtento\ProductExport\Model\ResourceModel\Profile');
    }
}