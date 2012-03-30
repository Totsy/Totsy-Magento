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
class Harapartners_SpeedTax_Block_Adminhtml_System_Config_Form_Field_Export extends Mage_Adminhtml_Block_System_Config_Form_Field {
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
		$buttonBlock = $this->getLayout ()->createBlock ( 'adminhtml/widget_button' );
		$params = array ('website' => $buttonBlock->getRequest ()->getParam ( 'website' ) );
		
		$data1 = array (
				'label' => Mage::helper ( 'adminhtml' )->__ ( 'Export Call Log' ), 
				'onclick' => 'setLocation(\'' . Mage::helper ( 'adminhtml' )->getUrl ( "speedtax/adminhtml_log_call/exportCsv", $params ) . '\')', 
				'class' => '', 
				'note' => Mage::helper ( 'speedtax' )->__ ( 'Test Connection' ) );
		
		$data2 = array(
            'label'     => Mage::helper('adminhtml')->__('Export Error Log'),
            'onclick'   => 'setLocation(\''.Mage::helper('adminhtml')->getUrl("speedtax/adminhtml_log_error/exportCsv", $params) . '\')',
            'class'     => '',
        );
		
		$html = $buttonBlock->setData ( $data1 )->toHtml (). ' &nbsp; ';
        $html .= $buttonBlock->setData($data2)->toHtml();
		
		return $html;
	}
}
