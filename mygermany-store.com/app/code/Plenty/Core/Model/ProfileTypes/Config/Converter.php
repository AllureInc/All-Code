<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Plenty\Core\Model\ProfileTypes\Config;
/**
 * Class Converter
 * @package Plenty\Core\Model\ProfileTypes\Config
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Get node value
     *
     * @param \DOMNode $input
     * @param string $attributeName
     * @param string|null $default
     * @return null|string
     */
    protected function _getNodeValue(\DOMNode $input, $attributeName, $default = null)
    {
        $node = $input->attributes->getNamedItem($attributeName);
        return $node ? $node->nodeValue : $default;
    }

    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $output = [];
        $xpath = new \DOMXPath($source);
        $types = $xpath->evaluate('/config/type');
        /** @var $typeNode \DOMNode */
        foreach ($types as $typeNode) {
            $typeName = $this->_getNodeValue($typeNode, 'name');
            $data = [];
            $data['name'] = $typeName;
            $data['label'] = $this->_getNodeValue($typeNode, 'label', '');
            $data['model'] = $this->_getNodeValue($typeNode, 'modelInstance');
            $data['sort_order'] = (int)$this->_getNodeValue($typeNode, 'sortOrder', 0);

            /** @var $childNode \DOMNode */
            foreach ($typeNode->childNodes as $childNode) {
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                /** @var $adaptorTypes \DOMNode */
                foreach ($childNode->childNodes as $entityType) {
                    if ($entityType->nodeType != XML_ELEMENT_NODE) {
                        continue;
                    }
                    $name = $this->_getNodeValue($entityType, 'name');
                    $data['adapter'][$name] = [
                        'label' => $this->_getNodeValue($entityType, 'label'),
                        'model' => $this->_getNodeValue($entityType, 'modelInstance'),
                        'router' => $this->_getNodeValue($entityType, 'modelRouter')
                    ];
                }
            }
            $output['types'][$typeName] = $data;
        }

        return $output;
    }
}
