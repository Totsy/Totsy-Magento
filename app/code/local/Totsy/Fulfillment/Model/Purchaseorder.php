<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lhansen
 * Date: 4/22/13
 * Time: 11:26 AM
 * To change this template use File | Settings | File Templates.
 */

class Totsy_Fulfillment_Model_Purchaseorder extends Mage_Core_Model_Abstract {

    /**
     * Creates a very generic po without knowing what the variables.  
     * It takes the purchase order items separately from the po 
     * information.
     * 
     * @param array $po_info purchase order data
     * @param array $items purchase order items
     * @return Totsy_Fulfillment_Model_Purchaseorder
     */
    public function reformatPoData($po_info, $po_items) {
		
		if(!is_array($po_info) || !is_array($po_items)) {
			throw Exception ('Please provide an array for po info and/or po items');
		}
		
        $generic_po = new Varien_Object();
        
        $generic_po->setData($po_info);
        $generic_po->setData('items', $items);
        
        $generic_po = $this->setData($generic_po->getData());
        return $generic_po;
    }
}
