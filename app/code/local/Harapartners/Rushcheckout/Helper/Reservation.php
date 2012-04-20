<?php 

/**
 * Harapartners
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Harapartners License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.Harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@Harapartners.com so we can send you a copy immediately.
 *
 */

class Harapartners_Rushcheckout_Helper_Reservation extends Mage_Core_Helper_Abstract {
	
	const DEFAULT_WEBSITE_ID		= 1;
	const PRECISION_DELTA			= 0.00001;
	
	public function updateReservationByQuoteItem(Mage_Sales_Model_Quote_Item $quoteItem, $shouldRestock = false){
		//Child item does NOT carry stock info
		if(!!$quoteItem->getParentItem()){
			return $this;
		}
		
		if($shouldRestock){
			$deltaQty = (double) $quoteItem->getData('qty');
		}else{
			//Use 'last_reservation_qty' to prevent duplicate updates by duplicate saves (only saves with qty update will be effective)
			if(!!$quoteItem->getData('last_reservation_qty')){
				$origQty = $quoteItem->getData('last_reservation_qty');
			}else{
				$origQty = $quoteItem->getOrigData('qty');
			}
			if($quoteItem->isDeleted()){
				$newQty = 0.0;
			}else{
				$newQty = $quoteItem->getData('qty');
			}
			$deltaQty = -1.0 * ($newQty - $origQty); //Quote item qty changes count as negative toward stock qty
		}
		
		$options = $quoteItem->getQtyOptions();
		if ($options) {
            foreach ($options as $option) {
                $stockItem = $option->getProduct()->getStockItem();
                if (!$stockItem instanceof Mage_CatalogInventory_Model_Stock_Item) {
                    Mage::throwException(
                        Mage::helper('cataloginventory')->__('The stock item for Product in option is not valid.')
                    );
                }
                if($this->_validateStock($stockItem, $deltaQty)){
                	$this->_registerUpdate($stockItem, $deltaQty);
                	$quoteItem->setData('last_reservation_qty', $newQty);
            	}else{
            		Mage::throwException(
                        Mage::helper('cataloginventory')->__('The requested quantity for \'%s\' is not available.', $quoteItem->getProduct()->getName())
                    );
            	}
            }
		}else{
			$stockItem = $quoteItem->getProduct()->getStockItem();
			if($this->_validateStock($stockItem, $deltaQty)){
               	$this->_registerUpdate($stockItem, $deltaQty);
           	}else{
           		Mage::throwException(
                       Mage::helper('cataloginventory')->__('The requested quantity for \'%s\' is not available.', $quoteItem->getProduct()->getName())
                   );
           	}
		}
		return $this;
	}
    
	public function updateReservationByStockItem(Mage_CatalogInventory_Model_Stock_Item $stockItem){
		//True update only
		//Additional handled by product creation
		//Deletion can be safely ignored
		if(!!$stockItem->getOrigData('item_id')){
			//Use 'last_reservation_qty' to prevent duplicate updates by duplicate saves (only saves with qty update will be effective)
			if(!!$stockItem->getData('last_reservation_qty')){
				$origQty = $stockItem->getData('last_reservation_qty');
			}else{
				$origQty = $stockItem->getOrigData('qty');
			}
			$newQty = $stockItem->getData('qty');
			$deltaQty = (double) ($newQty - $origQty);
			$this->_registerUpdate($stockItem, $deltaQty);
			$stockItem->setData('last_reservation_qty', $newQty);
		}
		return $this;
	}
	
	protected function _validateStock($stockItem, $deltaQty){
		//Restock (positive delta) is always allowed
		if($deltaQty >= -1.0 * self::PRECISION_DELTA){
			return true;
		}
		
		//During split order, some stock reservation may be locked within a (yet to be committed) DB transction
		//Skip qty check (Only the true stock qty will be the limiting factor)
		if(Mage::registry('isSplitOrder')){
			return true;
		}
		
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$read->beginTransaction();
		$select = $read->select()
				->from('cataloginventory_stock_status')
				->where('product_id = ?', $stockItem->getData('product_id'))
				->where('website_id = ?', self::DEFAULT_WEBSITE_ID)
            	->where('stock_id = ?', $stockItem->getData('stock_id'));
        $result = $read->fetchRow($select);
        if(!empty($result['qty']) && $result['qty'] + $deltaQty >= 0){
        	return true;
        }
		return false;
    }
	
	protected function _registerUpdate($stockItem, $deltaQty){
		//No nill update
		if(abs($deltaQty) <= self::PRECISION_DELTA){
			return $this;
		}
		
		//No composite update
		if(in_array($stockItem->getProductTypeId(), $this->_getCompositeProductTypeArray())){
			return $this;
		}
		
		//Respecting admin flags
		if(!$stockItem->getManageStock()){
			return $this;
		}
		
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		$queryString = 'UPDATE `cataloginventory_stock_status` ';
		if($deltaQty >= -1.0 * self::PRECISION_DELTA){
			$queryString .= 'SET `qty` = `qty` + ' . abs($deltaQty) . ', `stock_status` = IF(`qty`>0, 1, 0) ';
		}else{
			$queryString .= 'SET `qty` = `qty` - ' . abs($deltaQty) . ', `stock_status` = IF(`qty`>0, 1, 0) ';
		}
		$queryString .= 'WHERE `product_id` = ' . $stockItem->getData('product_id') 
				. ' AND `website_id` = '  . self::DEFAULT_WEBSITE_ID 
				. ' AND `stock_id` = ' . $stockItem->getData('stock_id')  . ';';
		$write->query($queryString);
		
		return $this;
	}
	
	protected function _getCompositeProductTypeArray(){
//		$compositeProductTypeArray = array();
//		$types = Mage::getSingleton('catalog/product_type')->getTypesByPriority();
//		foreach ($types as $typeId => $typeInfo) {
//			if(!empty($typeInfo['composite'])){
//				$compositeProductTypeArray[] = $typeId;
//			}
//		}
		$compositeProductTypeArray = array('configurable', 'bundle', 'grouped');
        return $compositeProductTypeArray;
	}
	
}