<?php

class Unirgy_SimpleUp_Adminhtml_ModuleController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();

        if (Mage::getStoreConfig('usimpleup/general/check_ioncube') && !extension_loaded('ionCube Loader')) {
            Mage::getSingleton('adminhtml/session')->addNotice('ionCube Loader is not installed, commercial extensions might not work.');
        }
        if (!extension_loaded('zip')) {
            Mage::getSingleton('adminhtml/session')->addError('Zip PHP extension is not installed, will not be able to unpack downloaded extensions');
        }
        if (Mage::getStoreConfig('usimpleup/ftp/active') && !extension_loaded('ftp')) {
            Mage::getSingleton('adminhtml/session')->addError('FTP PHP extension is not installed, will not be able to install extensions using FTP');
        }

        $this->_setActiveMenu('system/usimpleup');
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Simple Upgrades'), Mage::helper('adminhtml')->__('Simple Upgrades'));

        $this->_addContent($this->getLayout()->createBlock('adminhtml/template')->setTemplate('usimpleup/container.phtml'));

        $this->renderLayout();
    }

    public function checkUpdatesAction()
    {
        try {
            $modules = $this->getRequest()->getPost('modules');
            Mage::helper('usimpleup')->checkUpdates();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Version updates have been fetched'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    public function postAction()
    {
        $action = $this->getRequest()->getPost('do');
        switch ($action) {
        case Mage::helper('usimpleup')->__('Download and Install'):
            $this->_forward('install');
            break;
        }

        if (($m = Mage::getConfig()->getNode('modules/Unirgy_SimpleLicense')) && $m->is('active')) {
            switch ($action) {
            case Mage::helper('usimplelic')->__('Add license key'):
                $this->_forward('addLicense', 'adminhtml_license', 'usimplelicadmin');
                break;
            }
        }
    }

    public function installAction()
    {
        try {
            $uris = $this->getRequest()->getPost('uri');
            foreach ($uris as $i=>$uri) if (!$uri) unset($uris[$i]);
            if (!$uris) {
                Mage::throwException($this->__('No modules to install'));
            }
            Mage::helper('usimpleup')->installModules($uris);
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('New modules has been downoaded and installed'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    public function massUpgradeAction()
    {
        try {
            $modules = $this->getRequest()->getPost('modules');
            if (!$modules) {
                Mage::throwException($this->__('No modules to upgrade'));
            }
            Mage::helper('usimpleup')->upgradeModules($modules);
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Modules have been upgraded'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    public function massUninstallAction()
    {
        try {
            $modules = $this->getRequest()->getPost('modules');
            if (!$modules) {
                Mage::throwException($this->__('No modules to uninstall'));
            }
            Mage::helper('usimpleup')->uninstallModules($modules);
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Modules have been uninstalled'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/tools/usimpleup');
    }

    public function gridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('usimpleup/adminhtml_module_grid')->toHtml()
        );
    }
}
