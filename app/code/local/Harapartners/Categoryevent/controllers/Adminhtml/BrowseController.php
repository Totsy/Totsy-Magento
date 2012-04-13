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
 
class Harapartners_Categoryevent_Adminhtml_BrowseController extends Mage_Adminhtml_Controller_Action{
	
	public function indexAction(){
		$this->_title($this->__('Category Event'))->_title($this->__('Browse Category Events'));
        $this->loadLayout();
        $this->renderLayout();
    }   
}