<?php
/**
 * OneStepCheckout
 *
 * @category   onestepcheckout
 * @package    Mangoit_Marketplace
 */
namespace Mangoit\Marketplace\Block\Frontend\LayoutProcessors;

class SubscribeProcessor extends \Onestepcheckout\Iosc\Block\Frontend\LayoutProcessors\SubscribeProcessor
{
    /**
     * {@inheritdoc}
     */
    public function process($jsLayout)
    {
        $configKey = 'subscribe';

        if ($this->helper->isEnabled() && isset($jsLayout['components']['checkout']['children']['iosc']['children'][$configKey])) {
            $customConfig = $jsLayout['components']['checkout']['children']['iosc']['children'][$configKey];
            unset($jsLayout['components']['checkout']['children']['iosc']['children'][$configKey]);

            $include = $this->getIsEnabled($configKey);

            if ($include) {
                if (isset($jsLayout['components']['checkout']['children']['sidebar']['children'][$configKey])) {
                    $componentConfig = $jsLayout['components']['checkout']['children']['sidebar']['children'][$configKey];
                    $componentConfig = array_merge($componentConfig, $customConfig);
                    $jsLayout['components']['checkout']['children']['sidebar']['children'][$configKey] = $componentConfig;
                }
            } else {
                if (isset($jsLayout['components']['checkout']['children']['sidebar']['children'][$configKey])) {
                    unset($jsLayout['components']['checkout']['children']['sidebar']['children'][$configKey]);
                }
            }
        }

        return $jsLayout;
    }

    private function getIsEnabled($configKey)
    {

        $include = false;
        $scopeStore = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $enabled = $this->scopeConfig->getValue('onestepcheckout_iosc/' . $configKey . '/enable', $scopeStore);

        if ($enabled) {
            $include = true;
        }

        $isLoggedIn = $this->customerSession->getId();
        if ($isLoggedIn) {
            $enabledForReg = $this->scopeConfig->getValue('onestepcheckout_iosc/' . $configKey . '/enableforreg', $scopeStore);
            $hideFromRegandSubscribed = $this->scopeConfig->getValue('onestepcheckout_iosc/' . $configKey . '/hidefromregandsubscribed', $scopeStore);

            if (!$enabledForReg) {
                $include = false;
            }

            if ($enabledForReg && $hideFromRegandSubscribed) {
                $status = $this->subscriber->loadByCustomerId($isLoggedIn);
                if ($status->getStatus() == 1) {
                    $include = false;
                }
            }
        }

        return $include;
    }
}
