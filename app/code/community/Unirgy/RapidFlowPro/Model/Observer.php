<?php

class Unirgy_RapidFlowPro_Model_Observer
{
    public function adminhtml_version($observer)
    {
        Mage::helper('urapidflow')->addAdminhtmlVersion('Unirgy_RapidFlowPro');
    }
}