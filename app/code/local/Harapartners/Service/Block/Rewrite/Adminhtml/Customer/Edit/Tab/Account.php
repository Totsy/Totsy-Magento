<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

class Harapartners_Service_Block_Rewrite_Adminhtml_Customer_Edit_Tab_Account extends Mage_Adminhtml_Block_Customer_Edit_Tab_Account {
   
	protected function _setFieldset($attributes, $fieldset){
		parent::_setFieldset($attributes, $fieldset);
		if(!!$fieldset->getForm()->getElement('login_counter')){
            $fieldset->getForm()->getElement('login_counter')->setDisabled('disabled');
        }
        return $this;
	}
	
}