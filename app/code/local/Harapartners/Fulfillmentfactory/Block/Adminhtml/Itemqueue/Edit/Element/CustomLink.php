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
class Harapartners_Fulfillmentfactory_Block_Adminhtml_Itemqueue_Edit_Element_CustomLink extends Varien_Data_Form_Element_Link {
	public function __construct($attributes = array()) {
		parent::__construct($attributes);
	}
	
	//to render cunstom link with parameter
	public function getElementHtml() {
        $html = $this->getBeforeElementHtml();
        $this->_data['href'] .= $this->_data['keyname'] . '/' . $this->getEscapedValue();
        $html .= '<a id="'.$this->getHtmlId().'" '.$this->serialize($this->getHtmlAttributes()).'>'. $this->getEscapedValue() . "</a>\n";
        $html .= $this->getAfterElementHtml();
        return $html;
	}
}