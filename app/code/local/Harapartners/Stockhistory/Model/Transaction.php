<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

class Harapartners_Stockhistory_Model_Transaction extends Mage_Core_Model_Abstract {
    
    const STATUS_PENDING = 1;
    const STATUS_PROCESSED = 2;
    const STATUS_FAILED = 3;
    
    const ACTION_TYPE_AMENDMENT = 1;
    const ACTION_TYPE_EVENT_IMPORT = 2;
    const ACTION_TYPE_DIRECT_IMPORT = 3;
    const ACTION_TYPE_REMOVE = 4;
    
    public function _construct() {
        $this->_init('stockhistory/transaction');
    }
    
    protected function _beforeSave(){
        parent::_beforeSave(); 
        if(!$this->getId()){
            $this->setData('created_at', now());
        }
        $this->setData('updated_at', now());
        
        if(!$this->getStoreId()){
            $this->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID);
        }
        $this->validate();
        return $this;
    }
    
    public function loadByProductId($productId) {
        $this->addData($this->getResource()->loadByProductId($productId));
        return $this;
    }
    
    public function importData($dataObj){
        //Type casting
        if(is_array($dataObj)){
            $dataObj = new Varien_Object($dataObj);
        }
        if(!($dataObj instanceof Varien_Object)){
            Mage::throwException('Invalid data type, Array or Varien_Object needed.');
        }
        
        $vendor = Mage::getModel('stockhistory/vendor');
        if(!!$dataObj->getdata('vendor_id')){
            $vendor->load($dataObj->getdata('vendor_id'));
        }elseif(!!$dataObj->getdata('vendor_code')){
            $vendor->loadByCode($dataObj->getdata('vendor_code'));
        }
        if(!$vendor || !$vendor->getId()){
            Mage::throwException('Invalid Vendor.');
        }
        $dataObj->setData('vendor_id', $vendor->getId());
        $dataObj->setData('vendor_code', $vendor->getVendorCode());
        
        //Load category
        $category = Mage::getModel('catalog/category');
        if(!!$dataObj->getdata('category_id')){
            $category->load($dataObj->getdata('category_id'));
        }
        if(!$category || !$category->getId()){
            Mage::throwException('Invalid Category/Event.');
        }
        $dataObj->setData('category_id', $category->getId());
        
        //Load product
        $product = Mage::getModel('catalog/product');
        if(!!$dataObj->getdata('product_id')){
            $product->load($dataObj->getdata('product_id'));
        }elseif(!!$dataObj->getdata('product_sku')){
            $product->loadByAttribute('sku', $dataObj->getdata('product_sku'));
        }
        if(!$product || !$product->getId()){
            Mage::throwException('Invalid Product.');
        }
        if($product->getTypeId() != 'simple'){
            Mage::throwException('Purchase should only contain simple product. Other product types are ignored.');
        }
        $dataObj->setData('product_id', $product->getId());
        $dataObj->setData('product_sku', $product->getSku());
        $dataObj->setData('vendor_style', $product->getVendorStyle());
        $dataObj->setData('unit_cost', $product->getSaleWholesale());
        
        if(!$dataObj->getData('action_type')){
            $dataObj->setData('action_type', self::ACTION_TYPE_AMENDMENT);
        }

        $sold = Mage::helper('stockhistory')->getIndProductSold($category, $product);
        #making sure that items with sold quantity can't be zeroed out
    	if($dataObj->getData('qty_delta') === '00' && !$sold) {
        	//put a flag to indicate this item should be delete remove this line item
        	$qtyDelta = 0;
        	$dataObj->setData('action_type', self::ACTION_TYPE_REMOVE);
        }
        
        $this->addData($dataObj->getData());
        return $this;
    }
    
    public function validate(){
        if(!$this->getData('vendor_id')){
            throw new Exception('Vendor ID is required.');
        }
        if(!$this->getData('po_id')){
            throw new Exception('Purchase order ID is required.');
        }
        if(!$this->getData('category_id')){
            throw new Exception('Category ID is required.');
        }
        
        if(!$this->getData('product_id')){
            throw new Exception('Product ID is required.');
        }
        $product = Mage::getModel('catalog/product')->load($this->getData('product_id'));
        if($product->getTypeId() != 'simple'){
            Mage::throwException('Purchase should only contain simple product. Other product types are ignored.');
        }
        $qtyDelta = $this->getData('qty_delta');
        if(!isset($qtyDelta)){
            throw new Exception('Qty Changed is required.');
        }
        if(!!$this->getData('unit_cost')){
            $unitCost = $this->getData('unit_cost');
            if($unitCost < 0){
                throw new Exception('Unit cost must be a non-negative number.');
            }
        }else{
            throw new Exception('Unit Cost is required.');
        }
        
        return $this;
    }
    
    public function updateProductStock(){
        $product = Mage::getModel('catalog/product')->load($this->getData('product_id'));
        if($product->getTypeId() != 'simple'){
            Mage::throwException('Purchase should only contain simple product. Other product types are ignored.');
        }

        $productData = $product->getData();

        $tempProductId = $this->getData('product_id');
        $category = Mage::getModel('catalog/category')->load($this->getData('category_id'));
        $sold = Mage::helper('stockhistory')->getProductSoldInfoByCategory($category, array( $tempProductId => $tempProductId ));
        
        #when an items stock qty is reduced to zero and it has sold items, the qty delta should be adjusted to avoid negative values
        if($sold && $sold[$tempProductId]['qty'] && ($this->getData('qty_delta') < 0) && (abs($this->getData('qty_delta')) == $this->getData('orig_qty_total')) ) {
                
                $qty_delta =  $sold[$tempProductId]['qty'] - $this->getData('orig_qty_total');
                $this->setQtyDelta($qty_delta);
               
        }

        $bool = (!empty($sold) && $sold[$tempProductId]['qty']) || $product->getData('is_master_pack');
        $bool = $bool && ($this->getData('action_type') == 4);

        if (!$bool) {
            $stock = $product->getStockItem();
            $qtyStock = $stock->getQty();
            $qtyDelta = $this->getData('qty_delta');
            if(($qtyStock + $qtyDelta) < 0){
                throw new Exception('This stock update will result in a negative value. Ignored.');
            }
            
            $stock->setQty($qtyStock + $qtyDelta);
            $stock->save();
            return true;
        } else {
            throw new Exception('Cannot remove item that ' . ($product->getData('is_master_pack')) ? 'is a case pack ' : 'has been sold');
        }
    }

    public function changeCasePackStatus($items, $changeto) {

        $product_collection = Mage::getModel('catalog/product')->getCollection();
        $product_collection->getSelect()->where('entity_id in (' . implode(',' , $items) . ')' );
        
        foreach($product_collection as $product) {
            $this->changeCasePackAttributeValue('is_masterpack', $product->getData('entity_id'), $changeto);
        }
    }

    public function changeCasePackAttributeValue($attribute, $product_id, $changeto) {

        $product = Mage::getModel('catalog/product')->load($product_id);
        if($product) {
            $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
            ->getParentIdsByChild($product->getData('entity_id'));
            $product->setData('_edit_mode', true);
            $product->setFulfillmentType('dotcom');
            if(!empty($parentIds)){
                $product->setVisibility(1);
            } else {
                $product->setVisibility(4);
            }
            switch($attribute) {
                case 'case_pack_grp_id':
                    $product->setCasePackGrpId($changeto);
                    break;
                case 'case_pack_qty':
                    $product->setCasePackQty($changeto);
                    break;
                case 'is_masterpack':
                    $product->setIsMasterPack((int)$changeto);
                    break;
            }
            $product->save();
            return true;       
        }
        return false;
    }

    public function calculateCasePackOrderQty($item_id, $po_id, $case_pack_grp_id, $all_results = false){
        
        $highest_ratio = 0;
        $grouped = array();
        $order_amount = 0;
        $ratio = 0;

        if(!isset($po_id)) {
            throw new Exception("PO ID does not exists.");
        }

        #init needed data
        $po = Mage::getModel('stockhistory/purchaseorder')->load($po_id);
        $_category = Mage::getModel('catalog/category')->load($po->getData('category_id'));

        #Most Items that does not have a case pack id
        if (empty($case_pack_grp_id) && $item_id) {
            $product = Mage::getModel('catalog/product')->load($item_id);
            if(!$product->getData('is_master_pack')) {
                return $order_amount;
            }

            $total_units_sold = Mage::helper('stockhistory')->getIndProductSold($_category, $product);

            if((int)$product->getData('case_pack_qty')) {
                $highest_ratio = ceil($total_units_sold/$product->getData('case_pack_qty'));
            }

            $order_amount = Mage::helper('stockhistory')->casePackOrderAmount($highest_ratio, $product->getData('case_pack_qty'));
            
            if($all_results) {
                $grouped[(string)$item_id] = array('sku' => $product->getData('sku'), 'qty_to_amend' => $order_amount, 'cp_qty' => $product->getData('case_pack_qty'));
                $grouped['message'][] = array('message' => 'Successfully Updated!', 'type' => 'success' );
                return $grouped;
            }

            return $order_amount;
            
        } else {
            #pull all the items that have the same case pack grp id and event id
            $products = Mage::getModel('catalog/product')->getCollection()
                    ->addCategoryFilter($_category)
                    ->addAttributeToFilter('type_id', 'simple')
                   // ->addAttributeToFilter(array(array('attribute'=>'is_master_pack', 'gt'=>0)))
                    ->addAttributeToSelect(array('case_pack_qty', 'is_master_pack'))
                    ->addAttributeToFilter(array(array('attribute' => 'case_pack_grp_id', 'eq' => $case_pack_grp_id)));
            foreach($products as $product) {
                if(!$product->getData('is_master_pack')) {
                    $grouped[(string)$product->getEntityId()] = array(
                        'sku' => $product->getData('sku'), 
                        'qty_to_amend' => $order_amount, 
                        'cp_qty' => ""
                    );
                    $grouped['message'][] = array('message' => "Sku : {$product->getData('sku')} is not a case pack", 'type' => 'warning');
                    //if($item_id == $product->getData('entity_id')) return $grouped;
                    continue;
                }

               $total_units_sold = Mage::helper('stockhistory')->getIndProductSold($_category, $product);

               if($product->getData('case_pack_qty')) {
                    $ratio = ceil($total_units_sold/$product->getData('case_pack_qty'));
               }

               #find highest denominator
               if ($highest_ratio < $ratio) {
                    $highest_ratio = $ratio;
                }

                $grouped[(string)$product->getEntityId()] = array(
                    'qty_sold' => $total_units_sold, 
                    'cp_qty' => $product->getData('case_pack_qty'), 
                    'sku' => $product->getData('sku'), 
                    'qty_to_amend' => $product->getData('case_pack_qty')
                );
            }

            if(!$highest_ratio) {
                if($all_results) {
                    $grouped['message'][] = array('message' => 'Successfully Updated!', 'type' => 'success' );
                    return $grouped;
                }
                
                return $grouped[$item_id]['cp_qty'];
            }

            foreach($grouped as $id => $values ) {
                if($id == "message") continue;
                $grouped[$id]['qty_to_amend'] = Mage::helper('stockhistory')->casePackOrderAmount($highest_ratio, $values['cp_qty']);

                if($id == $item_id){
                    $order_amount = $grouped[$id]['qty_to_amend'];
                }
            }
                        
            if($all_results){
                $grouped['message'][] = array('message' => 'Successfully Updated!', 'type' => 'success' );
                return $grouped;
            } else {
                return $order_amount;
            }
        } #end of else
    }
    
    public function massCasePackCalculation($po_id){
        $results = array();
        
        $po_items = Mage::getModel('stockhistory/transaction')->getCollection();
        $po_items->getSelect()->where('po_id=' . $po_id . ' and product_id is not null and action_type=2');
        foreach($po_items as $item){
            $product = Mage::getModel('catalog/product')->load($item->getData('product_id'));
            $case_pack_id = Mage::getResourceModel('catalog/product')->getAttributeRawValue($item->getData('product_id'), 'case_pack_grp_id', $product->getData('store_id'));
            $results[$item->getData('product_sku')] = $this->calculateCasePackOrderQty($item->getData('product_id'), $po_id, $case_pack_id);
        }
        return $results;
    }
    
}
