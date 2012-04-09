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
class Harapartners_Fulfillmentfactory_Block_Adminhtml_Itemqueue_Index_Renderer_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
	public function _getValue(Varien_Object $row) {
		$resultHtml = "";

        $format = $this->getColumn()->getFormat();

        $data = $row->getData($this->getColumn()->getIndex());
		
        $statusList = Mage::helper('fulfillmentfactory')->getItemqueueStatusDropdownOptionList();
        
        //show status label
        foreach ($statusList as $status) {
        	if($status['value'] == $data) {
        		$resultHtml = '<strong>' . $status['label'] . '</strong>';
        		break;
        	}
        }

        return $resultHtml;
	}
}