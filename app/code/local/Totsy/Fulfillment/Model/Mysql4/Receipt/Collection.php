<?php

/**
 * Class Totsy_Fulfillment_Model_Mysql4_Receipt_Collection
 * @author Lawrenberg Hanson <lhanson@totsy.com>
 */

class Totsy_Fulfillment_Model_Mysql4_Receipt_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct(){
        $this->_init('fulfillment/receipt');
    }
}