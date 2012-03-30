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

class Harapartners_Service_Block_Adminhtml_Widget_Grid_Column_Renderer_Category_Edit extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row){
//    	$attrCode = $this->getColumn()->getId();
//        if(!($row instanceof Mage_Catalog_Model_Product) 
//        		|| !$row->getId()
//        		|| !$row->getData($attrCode)){
//        	return '';
//        }
        
        $editUrl = $this->getUrl('*/catalog_product/edit', array(
			            'store'=>$this->getRequest()->getParam('store'),
			            'id'=>$row->getId()));
        $html = '<a alt="' . $this->htmlEscape($row->getName()) . '" title="' . $this->htmlEscape($row->getName()) . '" href="' . $editUrl . '">Edit</>';
        return $html;
    }
}