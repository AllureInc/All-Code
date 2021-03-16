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
 * @package   mirasvit/module-dashboard
 * @version   1.2.22
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Dashboard\Ui\Toolbar;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\AbstractComponent;
use Mirasvit\Report\Service\DateService;
use Magento\Framework\Stdlib\DateTime;
use Mirasvit\Report\Model\Config;

class Date extends \Mirasvit\Report\Ui\Component\Toolbar\Filter\Date
{
    public function prepare()
    {
        parent::prepare();

        $config = $this->getData('config');

//        $config = array_merge_recursive($config, [
//            'value' => [
//                'comparisonEnabled' => true,
//            ],
//        ]);

        $this->setData('config', $config);
    }
}
