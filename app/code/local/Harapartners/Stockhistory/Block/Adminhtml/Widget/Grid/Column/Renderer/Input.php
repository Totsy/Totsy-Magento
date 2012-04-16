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
    	//SKU
        $html = '<input type="text"';
        $html .= 'name="' . $this->getColumn()->getId() . '[' . $row->getData('sku') . '][' . $this->getColumn()->getId() . ']" ';
        $html .= 'value="' . $row->getData($this->getColumn()->getIndex()) . '"';
        $html .= 'class="input-text ' . $this->getColumn()->getId() . ' ' . $this->getColumn()->getInlineCss() . '"/>';
        
        //Unit cost
        $html .= '<input type="hidden"';
        $html .= 'name="' . $this->getColumn()->getId() . '[' . $row->getData('sku') . '][unit_cost]" ';
        $html .= 'value="' . $row->getData('unit_cost') . '"';
        $html .= 'class="input-text ' . $this->getColumn()->getId() . ' ' . $this->getColumn()->getInlineCss() . '"/>';
        return $html;
    }
    
}