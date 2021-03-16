<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Block;

/**
 * Class Preference
 * @package Scommerce\CookiePopup\Block
 */
class Preference extends \Magento\Framework\View\Element\Template
{
    /** @var \Magento\Framework\Stdlib\CookieManagerInterface */
    private $cookieManager;

    /** @var \Magento\Customer\Model\Session */
    private $customerSession;

    /** @var \Scommerce\CookiePopup\Model\Service\ChoiceService */
    private $choiceService;

    /** @var \Scommerce\CookiePopup\Helper\Data */
    private $helper;

    /** @var \Scommerce\CookiePopup\Api\Data\ChoiceSearchResultsInterface */
    private $choices;

    /** @var \Scommerce\CookiePopup\Api\Data\ChoiceSearchResultsInterface */
    private $customerChoices;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Customer\Model\Session $customerSession,
        \Scommerce\CookiePopup\Model\Service\ChoiceService $choiceService,
        \Scommerce\CookiePopup\Helper\Data $helper,
        array $data = []
    ) {
        $this->cookieManager = $cookieManager;
        $this->customerSession = $customerSession;
        $this->choiceService = $choiceService;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        return $this->helper->isEnabled() ? parent::_toHtml() : '';
    }

    /**
     * @return \Scommerce\CookiePopup\Block\Link
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLinkBlock()
    {
        return $this->getLayout()->getBlock('scommerce.cookiepopup.link');
    }

    /**
     * @return \Scommerce\CookiePopup\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * Check if has any choices
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function hasChoices()
    {
        return (bool) $this->getChoices()->getTotalCount();
    }

    /**
     * Check if has any customer choices
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function hasCustomerChoices()
    {
        return (bool) $this->getCustomerChoices()->getTotalCount();
    }

    /**
     * Get full choices list
     *
     * @return \Scommerce\CookiePopup\Api\Data\ChoiceInterface[]|\Scommerce\CookiePopup\Model\Data\Choice[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getChoiceList()
    {
        return $this->getChoices()->getItems();
    }

    /**
     * @return \Scommerce\CookiePopup\Api\Data\ChoiceInterface[]|\Scommerce\CookiePopup\Model\Data\Choice[]|\Scommerce\CookiePopup\Model\Data\Link[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerChoiceList()
    {
        return $this->getCustomerChoices()->getItems();
    }

    /**
     * Get url for save customer choice
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->helper->getSaveUrl();
    }

    /**
     * Is current customer logged in
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * @param string $str
     * @return array
     */
    public function getUsedByList($str)
    {
        return explode(PHP_EOL, $str);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConfig()
    {
        $choices = [];
        foreach ($this->getChoiceList() as $choice) {
            $index = $choice->getChoiceName();
            $ch = $choice->toArray();
            $ch['used_by_list'] = $this->getUsedByList($ch['list']);
            $ch['checked'] =
                $choice->getIsRequired() ||
                $this->cookieManager->getCookie($choice->getCookieName()) == "1" ||
                ($this->cookieManager->getCookie($choice->getCookieName()) === false &&
                    $choice->getDefaultState()
                )
            ;
            $choices[$index] = $ch;
        }
        $customerChoices = [];
        foreach ($this->getCustomerChoiceList() as $choice) {
            $customerChoices[$choice->getChoiceName()] = $choice->toArray();
        }
        return [
            'modal' => [
                'title' => $this->helper->getModalTitle(),
                'button_text' => $this->helper->getButtonText(),
                'header' => [
                    'background_color' => $this->helper->getHeaderBackgroundColor(),
                    'color' => $this->helper->getHeaderFontColor(),
                ],
                'footer' => [
                    'background_color' => $this->helper->getFooterBackgroundColor(),
                    'color' => $this->helper->getFooterFontColor(),
                ],
                'numberTabs' => $this->helper->getStyleFlag('number_tabs'),
                'hasRequiredText' => (bool)$this->helper->getStyleValue('required_cookie_option_text'),
            ],
            'list_header' => $this->helper->getListHeader(),
            'choices' => $choices,
            'getChoicesUrl' => $this->helper->getChoicesUrl(),
            'customer_choices' => $customerChoices,
            'first_tab' => $this->helper->hasFirstTab() ?
                [
                    'title' => $this->helper->getFirstTabTitle(),
                    'content' => $this->helper->getFirstTabText()
                ] : false,
            'last_tab' => $this->helper->hasLastTab() ?
                [
                    'title' => $this->helper->getMoreInfoTitle(),
                    'link' => $this->helper->getMoreInfoLink()
                ] : false,
        ];
    }

    /**
     * Load choices list
     *
     * @return \Scommerce\CookiePopup\Api\Data\ChoiceSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getChoices()
    {
        if ($this->choices === null) {
            $this->choices = $this->choiceService->getList();
        }
        return $this->choices;
    }

    /**
     * Load customer choices
     *
     * @return \Scommerce\CookiePopup\Api\Data\ChoiceSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCustomerChoices()
    {
        if ($this->customerChoices === null) {
            $this->customerChoices = $this->choiceService->getCustomerChoices();
        }
        return $this->customerChoices;
    }

    public function getPreparedChioices()
    {
        $choices = [];
        foreach ($this->getChoiceList() as $choice) {
            $index = $choice->getChoiceName();
            $ch = $choice->toArray();
            $ch['used_by_list'] = $this->getUsedByList($ch['list']);
            $ch['checked'] =
                $choice->getIsRequired() ||
                $this->cookieManager->getCookie($choice->getCookieName()) == "1" ||
                ($this->cookieManager->getCookie($choice->getCookieName()) === false &&
                    $choice->getDefaultState()
                )
            ;
            $choices[$index] = $ch;
        }
        $customerChoices = [];
        foreach ($this->getCustomerChoiceList() as $choice) {
            $customerChoices[$choice->getChoiceName()] = $choice->toArray();
        }
        return [
            "choices" => $choices,
            "customerChoices" => $customerChoices
        ];
    }
}
