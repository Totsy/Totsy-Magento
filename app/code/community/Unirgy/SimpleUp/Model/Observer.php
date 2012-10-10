<?php

class Unirgy_SimpleUp_Model_Observer
{
    public function controller_action_predispatch(Varien_Event_Observer $observer)
    {
        if (Mage::getSingleton('admin/session')->isLoggedIn()) {
            Mage::helper('usimpleup')->checkUpdatesScheduled();
        }
    }
}