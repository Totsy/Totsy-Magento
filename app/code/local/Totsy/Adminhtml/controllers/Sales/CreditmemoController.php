<?php
require_once 'Mage/Adminhtml/controllers/Sales/CreditmemoController.php';

class Totsy_Adminhtml_Sales_CreditmemoController extends Mage_Adminhtml_Sales_CreditmemoController {

	public function importAction() {

        $this->loadLayout();//->_setActiveMenu('promotions/banner');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('adminhtml/sales_creditmemo_import'));
        $this->renderLayout();
    }

    public function importcsvAction(){
		try {

            $bannerModel = Mage::getModel('promotions/banner');
            $bannerModel->importData($data)->save();

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('promotions')->__('Save success.'));

            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/import',);
            }else{
                $this->_redirect('*/*/');
            }
            return;

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/import');
            return;
        }

    }
}

?>