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

class Harapartners_Promotionfactory_Model_Mysql4_Virtualproductcoupon extends Mage_Core_Model_Mysql4_Abstract{
    
    protected function _construct(){
        $this->_init('promotionfactory/virtualproductcoupon', 'entity_id');
    }
    
    //Return null when there is no coupon setup for the virtual product (i.e. no reservation logic required)
    //Return an empty object (without ID) when there is nothing available
	public function loadOneByProductId( $productId, $status = null ) {
		//If there is no coupon code associated with this, the product is a regular virtual product, just return false
		if($this->loadTotalCountByProductId($productId) == 0){
			return null;
		}
		
		$readAdapter = $this->_getReadAdapter();
        $select = $readAdapter->select()
        		->from($this->getMainTable())
                ->where( "product_id=:product_id");
        
        //Note '0' is a valid status
        if(isset($status)){
        	$select->where( "status=:status");
        	$result = $readAdapter->fetchRow($select, array('product_id' => $productId, 'status' => $status));
        }else{
        	$result = $readAdapter->fetchRow($select, array('product_id' => $productId));
        }
        
        if(!$result){
        	$result = array();
        }
        
    	return $result;
    }
    
    public function loadTotalCountByProductId($productId) {
		//Try to count first, if 0, return false
		$readAdapter = $this->_getReadAdapter();
        $select = $readAdapter->select()
                ->from($this->getMainTable(), array('coupon_count' => new Zend_Db_Expr('COUNT(entity_id)')))
                ->where( "product_id=:product_id");
        $result = $readAdapter->fetchRow($select, array('product_id' => $productId));
        
        if(isset($result['coupon_count'])){
        	return $result['coupon_count'];
        }
        
        return 0;
    }
    
	public function loadByCode($code){
		$readAdapter = $this->_getReadAdapter();
        $select = $readAdapter->select()
                ->from($this->getMainTable())
                ->where( "code=:code");
        $result = $readAdapter->fetchRow($select, array('code' => $code));
		if(!$result){
        	$result = array();
        }
        return $result;
    }
    
}