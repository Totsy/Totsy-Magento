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
    
    const DEFAULT_WEBSITE_ID        = 1;
    const PRECISION_DELTA            = 0.00001;
    
    public function updateReservationByQuoteItem(Mage_Sales_Model_Quote_Item $quoteItem, $shouldRestock = false){
        //Child item does NOT carry stock info
        if(!!$quoteItem->getParentItem()){
            return $this;
        }
        
        //$newQty will be 'last_reservation_qty' after successful update
        if($quoteItem->isDeleted()){
            $newQty = 0.0;
        }else{
            $newQty = $quoteItem->getData('qty');
        }
        
        if($shouldRestock){
            $deltaQty = (double) $quoteItem->getData('qty');
        }else{
            //Use 'last_reservation_qty' to prevent duplicate updates by duplicate saves (only saves with qty update will be effective)
            if($quoteItem->getData('last_reservation_qty') !== null){
                $origQty = $quoteItem->getData('last_reservation_qty');
            }else{
                $origQty = $quoteItem->getOrigData('qty');
            }
            
            $deltaQty = -1.0 * ($newQty - $origQty); //Quote item qty changes count as negative toward stock qty
        }
        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        $quantityPurchased = $quoteItem->getProduct()->getQuantityPurchasedByCustomer($customerId);

        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $cartItems = $quote->getAllVisibleItems();
        $qtyInCart = 0;
        foreach ($cartItems as $item) {
            if($item->getProductId() == $quoteItem->getProduct()->getId()) {
                $qtyInCart += $item->getQty();
            }
        }
        $product = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('purchase_max_sale_qty')
            ->addAttributeToFilter('entity_id', $quoteItem->getProduct()->getId())
            ->getFirstItem();
        if($product->getData('purchase_max_sale_qty')) {
            if($product->getData('purchase_max_sale_qty') < ($qtyInCart)
                || $quantityPurchased >= $product->getData('purchase_max_sale_qty')) {
                Mage::throwException(
                    Mage::helper('cataloginventory')->__('You have requested more than the authorized quantity.')
                );
            }
        }
        $options = $quoteItem->getQtyOptions();
        if ($options) {
            foreach ($options as $option) {
                $stockItem = $option->getProduct()->getStockItem();
                if (!$stockItem instanceof Mage_CatalogInventory_Model_Stock_Item) {
                	if(Mage::app()->getStore()->isAdmin()){
                		Mage::getSingleton('adminhtml/session')->addError(
                				Mage::helper('cataloginventory')->__('The stock item for Product in option is not valid.')
                		);
                	}else{
                		Mage::throwException(
	                        	Mage::helper('cataloginventory')->__('The stock item for Product in option is not valid.')
	                    );
                	}
                }
                if($this->_registerUpdate($stockItem, $deltaQty, true)){
                    $quoteItem->setData('last_reservation_qty', $newQty);
                }else{
                    Mage::throwException(
                            Mage::helper('cataloginventory')->__('The requested quantity for \'%s\' is not available.', $quoteItem->getProduct()->getName())
                    );
                }
            }
        }else{
            $stockItem = $quoteItem->getProduct()->getStockItem();
            if($this->_registerUpdate($stockItem, $deltaQty, true)){
            	$quoteItem->setData('last_reservation_qty', $newQty);
            }else{
            	if(Mage::app()->getStore()->isAdmin()){
                	Mage::getSingleton('adminhtml/session')->addError(
                			Mage::helper('cataloginventory')->__('The requested quantity for \'%s\' is not available according to the cart reservation logic. But might be still in stock.<br/>As admin, you can proceed. But the quantity changes will be ignored in cart reservation logic.', $quoteItem->getProduct()->getName())
                	);
                }else{
                	Mage::throwException(
                        	Mage::helper('cataloginventory')->__('The requested quantity for \'%s\' is not available.', $quoteItem->getProduct()->getName())
                	);
                }
            	
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
    
    protected function _validateStock($result, $deltaQty){
        //Restock (positive delta) is always allowed
        if($deltaQty >= self::PRECISION_DELTA){
            return true;
        }
        
        //During split order, some stock reservation may be locked within a (yet to be committed) DB transction
        //Skip qty check (Only the true stock qty will be the limiting factor)
        if(Mage::registry('isSplitOrder')){
            return true;
        }
        
        // == 0.00 is allowed, (-1.0 * self::PRECISION_DELTA)
        if(!empty($result['qty']) && $result['qty'] + $deltaQty >= -1.0 * self::PRECISION_DELTA){
            return true;
        }
        
        return false;
    }
    
    protected function _registerUpdate($stockItem, $deltaQty, $shouldValidate = false){
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
        
        //Wrapped into a DB transaction for failure rollback
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        
        $select = $write->select()
                ->from('cataloginventory_stock_status')
                ->where('product_id = ?', $stockItem->getData('product_id'))
                ->where('website_id = ?', self::DEFAULT_WEBSITE_ID)
                ->where('stock_id = ?', $stockItem->getData('stock_id'));
        $result = $write->fetchRow($select);
        
        if($shouldValidate && !$this->_validateStock($result, $deltaQty)){
            return false;
        }
        
        $write->beginTransaction();
        //SQL += or -= may cause problems with nested DB transactions
        $queryString = 'UPDATE `cataloginventory_stock_status` ';
        $queryString .= 'SET `qty` = ' . ($result['qty'] + $deltaQty);
        
        // == 0.00 is considered as out of stock
        if($result['qty'] + $deltaQty >= self::PRECISION_DELTA){
            $queryString .= ', `stock_status` = 1 ';
        }else{
            $queryString .= ', `stock_status` = 0 ';
        }
        $queryString .= 'WHERE `product_id` = ' . $stockItem->getData('product_id') 
                . ' AND `website_id` = '  . self::DEFAULT_WEBSITE_ID 
                . ' AND `stock_id` = ' . $stockItem->getData('stock_id')  . ';';
        $write->query($queryString);
        $write->commit();

        
        return $this;
    }
    
    protected function _getCompositeProductTypeArray(){
//        $compositeProductTypeArray = array();
//        $types = Mage::getSingleton('catalog/product_type')->getTypesByPriority();
//        foreach ($types as $typeId => $typeInfo) {
//            if(!empty($typeInfo['composite'])){
//                $compositeProductTypeArray[] = $typeId;
//            }
//        }
        $compositeProductTypeArray = array('configurable', 'bundle', 'grouped');
        return $compositeProductTypeArray;
    }
    
}