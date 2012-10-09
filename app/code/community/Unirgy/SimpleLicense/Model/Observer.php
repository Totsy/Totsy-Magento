<?php

class Unirgy_SimpleLicense_Model_Observer
{
    public function usimpleup_license_tabs($observer)
    {

        $ioncube = extension_loaded('ionCube Loader');
        $bcompiler = extension_loaded('bcompiler');
        if (!$ioncube && !$bcompiler) {
            Mage::getSingleton('adminhtml/session')->addError('ionCube Loader nor bcompiler are installed, uSimpleLicense is not activated.');
            return;
        }
/*
        if (!extension_loaded('ionCube Loader')) {
            Mage::getSingleton('adminhtml/session')->addError('ionCube Loader is not installed, uSimpleLicense is not activated.');
            return;
        }
*/
        $container = $observer->getEvent()->getContainer();

        $container->addTab('manage_licenses_section', array(
            'label'     => Mage::helper('usimplelic')->__('Manage Licenses'),
            'title'     => Mage::helper('usimplelic')->__('Manage Licenses'),
            'content'   => $container->getLayout()->createBlock('usimplelic/adminhtml_license_grid')->toHtml(),
        ));

        $container->addTab('add_licenses_section', array(
            'label'     => Mage::helper('usimplelic')->__('Add Licenses'),
            'title'     => Mage::helper('usimplelic')->__('Add Licenses'),
            'content'   => $container->getLayout()->createBlock('adminhtml/template')->setTemplate('usimplelic/add_licenses.phtml')->toHtml(),
        ));
    }
}