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
        
        $fh = fopen('/tmp/skItems.log','a');
        fwrite($fh, is_object($items)?'obejct..'.get_class($items):'something else'."\n\n");
        fclose($fh);

        foreach ($items as $item){
            Mage::getModel('product/outofstock')->adminAfterSaveUpdateQty($item);
        }
	}
}