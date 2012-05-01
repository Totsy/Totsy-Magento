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
class Harapartners_Service_Block_Rewrite_Adminhtml_Catalog_Product_Edit_Tab_Attributes extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Attributes
{
	//overwrite to disable sku textfield
	protected function _setFieldset($attributes, $fieldset){
		parent::_setFieldset($attributes, $fieldset);
		$skuElement = $fieldset->getForm()->getElement('sku');
		$product = Mage::registry('current_product');
		if(!!$skuElement && !!$product && $product->getTypeId() == 'simple'){
            $skuElement->setData('readonly', 'readonly');
        }
        return $this;
	}
}
