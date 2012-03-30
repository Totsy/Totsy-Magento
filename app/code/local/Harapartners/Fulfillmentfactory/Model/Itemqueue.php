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
class Harapartners_Fulfillmentfactory_Model_Itemqueue extends Mage_Core_Model_Abstract {
	const STATUS_PENDING = 1;		// start from pending
	const STATUS_PARTIAL = 2;		// when item is partially filled
	const STATUS_READY = 3;			//when item is completely filled
	//For item in 'processing' should be 'locked', i.e. do not modify while it's still in processing
	const STATUS_PROCESSING = 4;	// when all items in the related order are filled and order is being processed (capture payment)
	const STATUS_SUSPENDED = 5;		// when order is holded
	const STATUS_SUBMITTED = 6;		// when order is submitted to DOTcom
	const STATUS_CLOSED = 7;		// when order is closed
	const STATUS_CANCELLED = 8;		// when order has been cancelled
	
	protected function _construct(){
        $this->_init('fulfillmentfactory/itemqueue');
    }
	
	protected function _beforeSave() {
    	//Timezone manipulation ignored. Use Magento default timezone (UTC)
		$datetime = date('Y-m-d H:i:s');
    	if(!$this->getId()){
    		$this->setData('created_at', $datetime);
    	}
    	$this->setData('updated_at', $datetime);
    	if(!$this->getStoreId()){
    		$this->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID);
    	}
    	
    	if(($validateResult = $this->validateData()) !== true) {
    		throw new Exception((string) $validateResult);
    	}
    	
    	parent::_beforeSave();
    }
    
    /**
     * Load item queue object by order item id
     *
     * @param int $orderItemId
     * @return item queue object
     */
	public function loadByItemId($orderItemId){
        $this->addData($this->getResource()->loadByOrderItemId($orderItemId));
        return $this;
    }
    
    /**
     * validate before save
     *
     * @return bool
     */
    public function validateData(){
    	//validate requird field
    	if(!$this->getData('order_item_id') || 
    	   !$this->getData('order_id') || 
    	   !$this->getData('store_id') || 
    	   !$this->getData('product_id')) {
    	   	return 'Required field is missing!';
    	}
    	
    	$qtyOrdered = $this->getData('qty_ordered');
    	$fulfillCount = $this->getData('fulfill_count');
    	$status = $this->getData('status');
    	
    	if(($qtyOrdered >= $fulfillCount) && ($fulfillCount >= 0)){
    		if(($qtyOrdered != $fulfillCount) && ($status == self::STATUS_READY)) {
    			return 'Fulfill count is not matching with status!';
    		}
    		
    		return true;
    	}else{
    		return 'Invalid fulfill count for item queue object!';
    	}
    	
    	//could put more validation logic if necessary.
    }
    
    /*
     * get array list of Status (for dropdown list)
     */
    public function getStatusList() {
    	return array(
    		array(
    			'label' => 'Pending',
    			'value' => self::STATUS_PENDING
    		),
    		array(
    			'label' => 'Partial filled',
    			'value' => self::STATUS_PARTIAL
    		),
    		array(
    			'label' => 'Ready',
    			'value' => self::STATUS_READY
    		),
    		array(
    			'label' => 'Processing',
    			'value' => self::STATUS_PROCESSING
    		),
    		array(
    			'label' => 'Suspended',
    			'value' => self::STATUS_SUSPENDED
    		),
    		array(
    			'label' => 'Submitted',
    			'value' => self::STATUS_SUBMITTED
    		),
    		array(
    			'label' => 'Complete',
    			'value' => self::STATUS_CLOSED
    		),
    		array(
    			'label' => 'Cancelled',
    			'value' => self::STATUS_CANCELLED
    		),
    	);
    }
}