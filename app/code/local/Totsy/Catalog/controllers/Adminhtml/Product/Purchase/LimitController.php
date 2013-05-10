<?php

class Totsy_Catalog_Adminhtml_Product_Purchase_LimitController extends Mage_Adminhtml_Controller_Action {

	public function indexAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('totsy_catalog/adminhtml_product_purchase_limit_edit'));
        $this->renderLayout();
    }

    public function importAction(){
		try {
            
            Mage::getModel('totsy_catalog/product_purchase_limit')->import();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('totsy_catalog')->__('Save success.'));

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