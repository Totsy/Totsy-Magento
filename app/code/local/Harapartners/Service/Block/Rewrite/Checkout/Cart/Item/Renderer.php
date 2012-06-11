<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license [^]
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 *
 */
class Harapartners_Service_Block_Rewrite_Checkout_Cart_Item_Renderer extends Mage_Checkout_Block_Cart_Item_Renderer {
    
	//Harapartners, Jun: add reservation code to cart renderer
	public function getOptionList() {
		$optionList = $this->getProductOptions();
		if($this->getItem()->getProduct()->isVirtual()){
			$reservationCodeOption = $this->getItem()->getOptionByCode('reservation_code');
			if($reservationCodeOption instanceof Mage_Sales_Model_Quote_Item_Option){
				$optionList[] = array(
    					'label' => 'Reservation Code', 
    					'value' => $reservationCodeOption->getValue()
    			);
			}
		}
        return $optionList;
    }
 
}