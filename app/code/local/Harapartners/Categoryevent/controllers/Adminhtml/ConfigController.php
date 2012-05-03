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

class Harapartners_Categoryevent_Adminhtml_ConfigController extends Mage_Adminhtml_Controller_Action{
    
    public function indexAction(){
        $this->loadLayout()
            ->_addContent($this->getLayout()->createBlock('categoryevent/adminhtml_config_edit'))
            ->renderLayout();
    }
    
    public function saveAction(){
        $config = Mage::getModel('core/config');
        $pageConfig = $this->getRequest()->getPost('pagenumber_config');
        $configKey = 'pagenumber_config';        
        $config->saveConfig('config/catalog_page_number/'.$configKey, $pageConfig);
        
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('categoryevent')->__('Configuration saved.'));
        
        /* useing following code to get the switch config value
         * $testing = Mage::getStoreConfig('config/coupon_switch/on_and_off'); 
        */
                
        $this->_redirect('*/*/index');
    }
        
}