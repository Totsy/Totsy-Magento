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

class Harapartners_Stockhistory_Block_Adminhtml_Widget_Grid_Column_Renderer_Itemlink extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {

    public function render(Varien_Object $row){
        
        //qty_to_amend: defined as FINAL quantity, i.e. forcing the quantity to the given value, instead of just the delta
        $html = '<a href="';
        $html .= Mage::getUrl('catalog_product/edit/store/1/id/' . $row->getData('product_id')) . '"target="_blank">';
        $html .= $row->getData('product_id') . "</a>";
        return $html;
    }

    protected function _isAllowedAction($action)
    {
        //return null;
        return Mage::getSingleton('admin/session')->isAllowed('harapartners/stockhistory/purchaseorder/actions/' . $action);
    }
    
}
