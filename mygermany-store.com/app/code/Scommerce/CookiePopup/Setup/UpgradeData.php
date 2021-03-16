<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Scommerce\CookiePopup\Api\Data\ChoiceInterface;
use Scommerce\CookiePopup\Api\Data\LinkInterface;
use Scommerce\CookiePopup\Model\Data\Choice;
use Scommerce\CookiePopup\Model\Data\Link;

/**
 * Class UpgradeData
 * @package Scommerce\CookiePopup\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /** @var \Scommerce\CookiePopup\Model\ChoiceFactory */
    private $choiceFactory;

    /** @var \Scommerce\CookiePopup\Model\ChoiceRepository */
    private $choiceRepository;

    /**
     * UpgradeSchema constructor.
     * @param \Scommerce\CookiePopup\Model\ChoiceFactory $choiceFactory
     * @param \Scommerce\CookiePopup\Model\ChoiceRegistry $choiceRepository
     */
    public function __construct(
        \Scommerce\CookiePopup\Model\ChoiceFactory $choiceFactory,
        \Scommerce\CookiePopup\Model\ChoiceRepository $choiceRepository
    ) {
        $this->choiceFactory = $choiceFactory;
        $this->choiceRepository = $choiceRepository;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $data = [
                'choice_name' => 'Strictly Necessary Cookies',
                'choice_description' =>
                    'These cookies are essential for the website to function and 
                    they cannot be turned off. They are usually only set in response 
                    to actions made by you on our site, such as logging in, adding items 
                    to your cart or filling in forms. If you browse our website, you accept these cookies.',
                'cookie_name' => 'required_cookies',
                'list' => 'Magento Store',
                'required' => 1,
                'default_state' => 1,
                'stores' => [0],
            ];
            $choice = $this->choiceFactory->create();
            $choice->addData($data);
            $this->choiceRepository->save($choice);
        }
    }
}
