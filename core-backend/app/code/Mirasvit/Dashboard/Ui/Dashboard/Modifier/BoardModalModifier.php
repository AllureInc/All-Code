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



namespace Mirasvit\Dashboard\Ui\Dashboard\Modifier;

use Magento\Framework\UrlInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Framework\Stdlib\ArrayManager;

class BoardModalModifier implements ModifierInterface
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        ArrayManager $arrayManager,
        UrlInterface $urlBuilder
    ) {
        $this->arrayManager = $arrayManager;
        $this->urlBuilder = $urlBuilder;
    }

    public function modifyData(array $data)
    {
        return $data;
    }

    public function modifyMeta(array $meta)
    {
        return $this->arrayManager->set('board_form_modal', $meta, [
            'arguments' => [
                'data' => [
                    'config' => [
                        'isTemplate'    => false,
                        'componentType' => 'modal',
                        'cssclass'      => 'modal',
                    ],
                ],
            ],
            'children'  => [
                'form' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType'    => 'container',
                                'component'        => 'Magento_Ui/js/form/components/insert-form',
                                'cssclass'         => 'none',
                                'dataScope'        => 'board',
                                'update_url'       => $this->urlBuilder->getUrl('mui/index/render'),
                                'render_url'       => $this->urlBuilder->getUrl('mui/index/render_handle', [
                                    'handle'  => 'dashboard_board_form',
                                    'buttons' => 1,
                                ]),
                                'autoRender'       => false,
                                'ns'               => 'dashboard_board_form',
                                'externalProvider' => 'dashboard_board_form.dashboard_board_form_data_source',
                                'toolbarContainer' => '${ $.parentName }',
                                'formSubmitType'   => 'ajax',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}