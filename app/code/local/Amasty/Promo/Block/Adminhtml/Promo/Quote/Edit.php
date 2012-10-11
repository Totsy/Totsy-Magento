<?php
/**
 * @copyright   Copyright (c) 2009-11 Amasty
 */
class Amasty_Promo_Block_Adminhtml_Promo_Quote_Edit extends Mage_Adminhtml_Block_Promo_Quote_Edit
{
    public function __construct()
    {
        parent::__construct();
        
        $this->_formScripts[] = " 
			function ampromo_hide() {
				$('rule_discount_qty').up().up().show();
				$('rule_discount_step').up().up().show();
				$('rule_apply_to_shipping').up().up().show();
				$('rule_actions_fieldset').up().show();
				$('rule_promo_sku').up().up().hide();
			
				if ('ampromo_cart' == $('rule_simple_action').value) {
				    $('rule_actions_fieldset').up().hide(); 
					$('rule_discount_qty').up().up().hide();
					$('rule_discount_step').up().up().hide();
					
					$('rule_apply_to_shipping').up().up().hide();
					$('rule_promo_sku').up().up().show();
				} 
				if ('ampromo_items' == $('rule_simple_action').value){
					$('rule_apply_to_shipping').up().up().hide();
					$('rule_promo_sku').up().up().show();
				}
			}
			ampromo_hide();
        ";
    }
}