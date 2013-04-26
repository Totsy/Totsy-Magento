<?php

class Totsy_Reward_Adminhtml_ImportController extends Mage_Adminhtml_Controller_Action {

	public function indexAction() {
        $this->loadLayout();//->_setActiveMenu('promotions/banner');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('totsy_reward/adminhtml_import_edit'));
        $this->renderLayout();
    }

    public function importAction(){
		try {
            
            Mage::getModel('enterprise_reward/import')->import();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('enterprise_reward')->__('Save success.'));

            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/index');
            }else{
                $this->_redirect('*/*/');
            }
            return;

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/index');
            return;
        }

    }
}

?>