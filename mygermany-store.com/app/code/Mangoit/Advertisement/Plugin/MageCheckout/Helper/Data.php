<?php
 
namespace Mangoit\Advertisement\Plugin\MageCheckout\Helper;
 
class Data
{
    protected $request;
    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
       $this->request = $request;
    }

    public function afterisAllowedGuestCheckout(\Magento\Checkout\Helper\Data $subject, $result)
    {
        $params = $this->request->getParams();
        if(isset($params['is_adv_preview']) && $params['is_adv_preview'] == 1){
            return true;
        }

        return $result;
    }
}
