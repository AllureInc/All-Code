<?php
/**
 * A Magento 2 module named Mangoit/TranslationSystem
 * Copyright (C) 2017 Mango IT Solutions
 * 
 * This file is part of Mangoit/TranslationSystem.
 * 
 * Mangoit/TranslationSystem is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Mangoit\TranslationSystem\Controller\Index;

use Magento\Framework\App\RequestInterface;

class Translate extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;
    protected $_url;
    protected $_session;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Model\Url $url
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_session = $session;
        $this->_url = $url;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_url->getLoginUrl();
        if (!$this->_session->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $customerSetup = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface')->getById(43);
        // echo "<pre>"; 
        // print_r($customerSetup->getCustomAttribute('is_translated')->getValue()); 

        $pageFactory = $this->resultPageFactory->create();
        $pageFactory->getConfig()->getTitle()->set('Translate your shop');
        return $pageFactory;
    }
}
