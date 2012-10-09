<?php

class Unirgy_SimpleLicense_Adminhtml_LicenseController extends Mage_Adminhtml_Controller_Action
{
    public function addLicenseAction()
    {
        try {
            $key = $this->getRequest()->getPost('license_key');
            $install = !!$this->getRequest()->getPost('download_install');
            Unirgy_SimpleLicense_Helper_Protected::retrieveLicense($key, $install);
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('usimplelic')->__('The license has been added: %s', $key));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('usimpleupadmin/adminhtml_module/');
    }

    public function checkUpdatesAction()
    {
        try {
            $licenses = Mage::getModel('usimplelic/license')->getCollection();
            foreach ($licenses as $license) {
                Unirgy_SimpleLicense_Helper_Protected::retrieveLicense($license);
                try {
                    Unirgy_SimpleLicense_Helper_Protected::validateLicense($license);
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('License updates have been fetched'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('usimpleupadmin/adminhtml_module/');
    }

    public function serverInfoAction()
    {
        try {
            Unirgy_SimpleLicense_Helper_Protected::sendServerInfo();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Server Info has been sent'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('usimpleupadmin/adminhtml_module/');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/tools/usimpleup');
    }

    public function gridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('usimplelic/adminhtml_license_grid')->toHtml()
        );
    }

    public function massRemoveAction()
    {
        try {
            $ids = $this->getRequest()->getPost('licenses');
            if (!$ids) {
                Mage::throwException($this->__('No licenses to remove'));
            }
            $licenses = Mage::getModel('usimplelic/license')->getCollection()->addFieldToFilter('license_id', $ids);
            foreach ($licenses as $l) {
                $l->delete();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Licenses have been removed'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('usimpleupadmin/adminhtml_module/');
    }
}