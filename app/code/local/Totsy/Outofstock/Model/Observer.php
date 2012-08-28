<?php
/**
 * @category    Totsy
 * @package     Totsy_Outofstock_Model
 * @author      Slavik Koshelevskyy <skosh@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Outofstock_Model_Observer {
	public function catalogProductUpdate ($observer){	
		$items = $observer->getEvent()->getOrder()->getAllItems();   

        foreach ($items as $item){
            Mage::helper('product/outofstock')->adminAfterSaveUpdateQty($item);
        }
	}
}