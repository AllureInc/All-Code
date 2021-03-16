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



namespace Mirasvit\Dashboard\Ui;

use Magento\Framework\View\Element\UiComponentInterface;

trait ComponentTrait
{
    public function prepareComponent(UiComponentInterface $component)
    {
        $data = [];
        foreach ($component->getChildComponents() as $child) {
            $data['children'][$child->getName()] = $this->prepareComponent($child);
        }

//        $component->prepare();

        $data['arguments']['data'] = $component->getData();
        if (!isset($data['arguments']['data']['config']['formElement'])) {
            $data['arguments']['data']['config']['formElement'] = $component->getComponentName();
        }
        if (!isset($data['arguments']['data']['config']['componentType'])) {
            $data['arguments']['data']['config']['componentType'] = 'field';
        }

        return $data;
    }
}