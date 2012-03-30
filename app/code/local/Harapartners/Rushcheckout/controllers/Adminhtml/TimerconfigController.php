<?php
class Harapartners_Rushcheckout_Adminhtml_TimerconfigController extends Mage_Adminhtml_Controller_Action{
	
	public function indexAction(){
		$this->loadLayout()
			->_setActiveMenu('harapartners/timerconfig')
			->_addContent($this->getLayout()->createBlock('rushcheckout/adminhtml_timerconfig_edit'))
			->renderLayout();
    }
    
	public function saveAction(){
		$config = Mage::getModel('core/config');
        $configValue = $this->getRequest()->getPost('timer_config');
        
//        foreach($timerConfig as $configKey => $configValue){
//        	$config->saveConfig('config/rushcheckout_timer/'.$configKey, $configValue);
//        }
//		for multiple input -- end

		$configKey = 'limit_timer';
		$config->saveConfig('config/rushcheckout_timer/'.$configKey, $configValue);
        
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('rushcheckout')->__('Configuration saved.'));
		
        /* useing following code to get the switch config value
         * $testing = Mage::getStoreConfig('config/coupon_switch/on_and_off'); 
		*/
                
        $this->_redirect('*/*/index');
    }
        
}