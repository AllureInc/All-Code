<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Ui\Component\Listing\Columns;

/**
 * Class ChoiceActions
 * @package Scommerce\CookiePopup\Ui\Component\Listing\Columns
 */
class ChoiceActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    const URL_PATH_EDIT     = 'scommerce_cookie_popup/manage/edit';
    const URL_PATH_DELETE   = 'scommerce_cookie_popup/manage/delete';

    /** @var \Magento\Framework\UrlInterface */
    protected $urlBuilder;

    /**
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (! isset($dataSource['data']['items'])) return $dataSource;
        foreach ($dataSource['data']['items'] as &$item) {
            if (! isset($item['choice_id'])) continue;
            $item[$this->getData('name')] = [
                'edit' => [
                    'href' => $this->urlBuilder->getUrl(
                        static::URL_PATH_EDIT,
                        ['choice_id' => $item['choice_id']]
                    ),
                    'label' => __('Edit'),
                ],
                'delete' => [
                    'href' => $this->urlBuilder->getUrl(
                        static::URL_PATH_DELETE,
                        ['choice_id' => $item['choice_id']]
                    ),
                    'label' => __('Delete'),
                    'confirm' => [
                        'title' => __('Delete "${ $.$data.name }"'),
                        'message' => __('Are you sure you wan\'t to delete the Choice "${ $.$data.name }" ?'),
                    ],
                ]
            ];
        }

        return $dataSource;
    }
}