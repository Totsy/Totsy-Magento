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

class Harapartners_Stockhistory_Model_Report extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('stockhistory/report');
    }
    
    protected function _beforeSave(){
        $now = date('Y-m-d H:i:s');
        if(!$this->getId()){
            $this->setData('created_at', $now);
        }
        $this->setData('updated_at', $now);
        
        if(!$this->getStoreId()){
            $this->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID);
        }
        parent::_beforeSave();  
    }
    
    public function loadByProductId($id)
    {
        $collection = Mage::getModel('stockhistory/report')->getCollection();
        $collection->getSelect()->where('product_id = ?', $id);
        return $collection->getFirstItem();
    }
    
    public function validateAndSave($data)
    {
        $this->addData($data);
        if(!$this->getData('product_id')){
            throw new Exception('Product ID is needed');
        }
        if(!$this->getData('category_id')){
            throw new Exception('Category ID is needed');
        }
        if(!!$this->getData('unit_cost')){
            $unitCost = $this->getData('unit_cost');
            if($unitCost < 0){
                throw new Exception('Please Enter a Positive number');
            }
        }else{
            throw new Exception('Unit Cost is needed');
        }
        $this->save();
        return $this;
    }
}