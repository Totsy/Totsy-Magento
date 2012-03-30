<?php
/**
 * Harapartners
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Harapartners License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.Harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@Harapartners.com so we can send you a copy immediately.
 *
 */

class Harapartners_Categoryevent_IndexController extends Mage_Core_Controller_Front_Action {
	
    public function indexAction(){
		$this->loadLayout();
		$this->renderLayout();		
    }
    
	public function topnavAction(){
		
		$attributeType = (string) $this->getRequest()->getParam('type', false);
		$attributeValue = (string) $this->getRequest()->getParam('value', false);
        if ((!$attributeType)||(!$attributeValue)) {
            return false;
        }
		Mage::register('attrtype', $attributeType);
		Mage::register('attrvalue', $attributeValue);
		$this->loadLayout();
		$this->renderLayout();
		
	}
	
    public function ageAction(){
    	$this->loadLayout();
    	$this->renderLayout();
    }
    
    public function categoryAction(){
    	$this->loadLayout();
    	$this->renderLayout();
    }
	

}
