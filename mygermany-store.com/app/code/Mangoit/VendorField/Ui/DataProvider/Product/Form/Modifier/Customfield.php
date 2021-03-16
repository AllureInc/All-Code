<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mangoit\VendorField\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
//use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\MultiSelect;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Element\DataType\Price;

class Customfield extends AbstractModifier
{

    // Components indexes
    const CUSTOM_FIELDSET_INDEX = 'custom_fieldset';
    const CUSTOM_FIELDSET_CONTENT = 'custom_fieldset_content';
    const CONTAINER_HEADER_NAME = 'container_header';

    // Fields names
    const FIELD_NAME_TEXT = 'example_text_field';
    const FIELD_NAME_SELECT = 'example_select_field';
    const FIELD_NAME_MULTISELECT = 'example_multiselect_field';

    const FIELD_IS_DELETE = 'is_delete';
    const CUSTOM_OPTIONS_LISTING = 'product_custom_options_listing';

    const CONTAINER_OPTION = 'container_option';
    const FIELD_SORT_ORDER_NAME = 'sort_order';
    const GROUP_CUSTOM_OPTIONS_SCOPE = 'data.product';
    const BUTTON_ADD = 'button_add';
    const GRID_OPTIONS_NAME = 'customfield_option';

    
    const FIELD_TYPE_NAME = 'custom_field_value';
    const FIELD_TYPE_PRICE = 'custom_fields';


    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    protected $locator;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var array
     */
    protected $meta = [];

    protected $currentProductId;

    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        UrlInterface $urlBuilder
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Data modifier, does nothing in our example.
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        $productId = $this->locator->getProduct()->getId();
        $this->currentProductId = $this->locator->getProduct()->getId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $counselorObj = $objectManager->create('Mangoit\VendorField\Model\Fieldmodel');
        $counselorCollection = $counselorObj->getCollection()
                                ->addFieldToFilter('product_id', array('eq' => $productId ));
        $counselorData = $counselorCollection->getData();

        if(sizeof($counselorData)) {
          foreach ($counselorData  as $value) {
           $options[$value['id']][static::FIELD_TYPE_NAME][]   = $value['custom_fields'];
           $options[$value['id']][static::FIELD_TYPE_PRICE][] = $value['custom_field_value'];
          }
          return array_replace_recursive(
            $data,
            [
                $this->locator->getProduct()->getId() => [
                  static::DATA_SOURCE_DEFAULT => [
                  // static::GRID_OPTIONS_NAME => $coptions,
                   static::GRID_OPTIONS_NAME => $options
                  ]
                ]
            ]
          );
        }
        
        return $data;
    }

    /**
     * Meta-data modifier: adds ours fieldset
     *
     * @param array $meta
     * @return counselor_pricearray
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;
        $this->createCustomOptionsPanel();

        return $this->meta;
    }

     protected function createCustomOptionsPanel()
    {
        $this->meta = array_replace_recursive(
            $this->meta,
            [
                static::CUSTOM_FIELDSET_INDEX => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Counselor Price'),
                                'componentType' => Fieldset::NAME,
                                'dataScope' => static::GROUP_CUSTOM_OPTIONS_SCOPE,
                                'collapsible' => true,
                                'sortOrder' => 10
                            ],
                        ],
                    ],
                    'children' => [
                     static::CONTAINER_HEADER_NAME => $this->getHeaderContainerConfig(10),
                     static::GRID_OPTIONS_NAME => $this->getOptionsGridConfig(30)
                        
                    ]
                ]
            ]
        );
       
        return $this;
    }

    protected function getHeaderContainerConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => null,
                        'formElement' => Container::NAME,
                        'componentType' => Container::NAME,
                        'template' => 'ui/form/components/complex',
                        'sortOrder' => $sortOrder,
                        'content' => __('Here you can add plan price counselor basis.'),
                    ],
                ],
            ],
            'children' => [
                static::BUTTON_ADD => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'title' => __('Add'),
                                'formElement' => Container::NAME,
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/form/components/button',
                                'sortOrder' => 20,
                                'actions' => [
                                    [
                                        'targetName' => 'ns = ${ $.ns }, index = ' . static::GRID_OPTIONS_NAME,
                                        'actionName' => 'processingAddChild',
                                    ]
                                ]
                            ]
                        ],
                    ],
                ],
            ],
        ];
    }


    protected function getOptionsGridConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel' => __('Add'),
                        'componentType' => DynamicRows::NAME,
                        'component' => 'Magento_Catalog/js/components/dynamic-rows-import-custom-options',
                        'template' => 'ui/dynamic-rows/templates/collapsible',
                        'additionalClasses' => 'admin__field-wide',
                        'deleteProperty' => static::FIELD_IS_DELETE,
                        'deleteValue' => '1',
                        'addButton' => false,
                        'renderDefaultRecord' => false,
                        'columnsHeader' => false,
                        'collapsibleHeader' => true,
                        'sortOrder' => $sortOrder,
                        'dataProvider' => static::CUSTOM_OPTIONS_LISTING,
                        'imports' => ['insertData' => '${ $.provider }:${ $.dataProvider }'],
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'headerLabel' => __('Custom Price'),
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => static::CONTAINER_OPTION . '.' . static::FIELD_SORT_ORDER_NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                            ],
                        ],
                    ],
                    'children' => [
                        static::CONTAINER_OPTION => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Fieldset::NAME,
                                        'collapsible' => true,
                                        'label' => null,
                                        'sortOrder' => 10,
                                        'opened' => true,
                                    ],
                                ],
                            ],
                            'children' => [
                                static:: FIELD_TYPE_NAME => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => [
                                                 'label' => __('Value'),
                                                 //'component' => 'Magento_Catalog/js/components/custom-options-price-type',
                                                 'componentType' => Field::NAME,
                                                 'formElement' => Select::NAME,
                                                 'dataScope' => static::FIELD_TYPE_NAME,
                                                 'dataType' => Text::NAME,
                                                 'sortOrder' => 40,
                                                 'validation' => [
                                                    'required-entry' => true
                                                ],
                                                 'options' => $this->_getOptions(),
                                                 'imports' => [
                                                     //'priceIndex' => self::FIELD_PRICE_NAME,
                                                 ],
                                             ],
                                        ],
                                     ],
                                  ],

                                 static::FIELD_TYPE_PRICE => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => [
                                                'componentType' => Field::NAME,
                                                'formElement' => Input::NAME,
                                                'dataType' => Price::NAME,
                                                'label' => __('Field Name'),
                                                'enableLabel' => true,
                                                'dataScope' => static::FIELD_TYPE_PRICE,
                                                'addbefore' => $this->locator->getStore()
                                                                             ->getBaseCurrency()
                                                                             ->getCurrencySymbol(),
                                                'sortOrder' => 30,
                                                'validation' => [
                                                    'required-entry' => true,
                                                    'validate-greater-than-zero' => true,
                                                    //'validate-number' => true,
                                                ],
                                                'value' => static::FIELD_TYPE_PRICE,
                                                'imports' => [
                                                    'priceValue' => '${ $.provider }:data.product.price',
                                                ],
                                            ],
                                        ],
                                    ],
                                  ],                               
                            ]
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * Get options as an option array:
     *      [
     *          label => string,
     *          value => option_id
     *      ]
     *
     * @return array
     */
    protected function _getOptions()
    {
      //  $objectManager = \Magento\Framework\App\ObjectManager::getInstance();      
      //  $userListCollection = $objectManager->get('\Mangoit\Collegewise\Helper\UserList');
      //  $userList = $userListCollection->userList();  //all admin user
      // $config_role = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('collegewise/general/enable');
        
      // $options = array();
      //   if(sizeof($userList)) {
      //   $options[] = ['value' => '', 'label' => 'Select Counselor'];        
      //        foreach ($userList as $key => $value) { 
      //           if($config_role == $value['role'] && $value['username']!='admin'){
      //               $options[] = ['value' => $value['id'], 'label' => $value['username']];   
      //           }
      //       }
      //   } 
        $options = [];
        array_push($options, ['value'=> 0, 'label'=> 'No']);
        array_push($options, ['value'=> 1, 'label'=> 'Yes']);
        // $options[] = ['value'=> 0, 'label'=> 'No'];
       return $options;
    }
}