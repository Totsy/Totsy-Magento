<?php

class Harapartners_Service_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isTotsyStore()
    {
        return Mage::app()->getStore()->getCode() == 'default';
    }
}
