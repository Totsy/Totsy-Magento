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

class Harapartners_Stockhistory_Block_Adminhtml_Widget_Grid_Column_Renderer_Input extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {

    public function render(Varien_Object $row){
        //SKU bases

        $readonly = "";

        if (!$this->_isAllowedAction('post_amendment')) {
            $readonly = "readonly";
        }

        
        //qty_to_amend: defined as FINAL quantity, i.e. forcing the quantity to the given value, instead of just the delta
        $html = '<input type="text"';
        $html .= 'id=' . $row->getData('sku');
        $html .= ' name="' . $this->getColumn()->getId() . '[' . $row->getData('sku') . '][' . $this->getColumn()->getId() . ']" ';
       // $html .= 'value="' . $row->getData($this->getColumn()->getIndex()) . '"';
        $html .= 'value="' . Mage::getModel('stockhistory/transaction')->calculateCasePackOrderQty($row->getData('product_id'), $row->getData('po_id'), $row->getData('case_pack_grp_id'))  . '"';
        $html .= 'class="input-text ' . $this->getColumn()->getId() . ' ' . $this->getColumn()->getInlineCss() . '"' . $readonly .'/>';
        
        //Current total qty
        $html .= '<input type="hidden"';
        $html .= 'name="' . $this->getColumn()->getId() . '[' . $row->getData('sku') . '][qty_total]" ';
        $html .= 'value="' . $row->getData('qty_total') . '"';
        $html .= 'class="input-text ' . $this->getColumn()->getId() . ' ' . $this->getColumn()->getInlineCss() . '"/>';
        
        //Unit cost
        $html .= '<input type="hidden"';
        $html .= 'name="' . $this->getColumn()->getId() . '[' . $row->getData('sku') . '][unit_cost]" ';
        $html .= 'value="' . $row->getData('unit_cost') . '"';
        $html .= 'class="input-text ' . $this->getColumn()->getId() . ' ' . $this->getColumn()->getInlineCss() . '"/>';
        
        return $html;
    }

    protected function _isAllowedAction($action)
    {
        //return null;
        return Mage::getSingleton('admin/session')->isAllowed('harapartners/stockhistory/purchaseorder/actions/' . $action);
    }
    
}
