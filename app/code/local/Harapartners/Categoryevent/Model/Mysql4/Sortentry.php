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


class Harapartners_Categoryevent_Model_Mysql4_Sortentry extends Mage_Core_Model_Mysql4_Abstract{
    
    protected $_read;
    
    protected function _construct(){
        $this->_init('categoryevent/sortentry', 'id');
        $this->_read = $this->_getReadAdapter();
        $this->_write = $this->_getWriteAdapter();
    }

    public function loadByAttribute($attrName, $attrValue, $storeId = null){

        $select = $this->_read->select()
            ->from($this->getMainTable())
            ->where($attrName . '=?', $attrValue);
            
        if(!!$storeId){
            $select->where('store_id=?', $storeId);
        }
        
        $rowData = $this->_read->fetchRow($select);
        
        if(!$rowData){
            $rowData = array();    
        }
        
        return $rowData;

    }
    
    public function loadLatestRecord($attrName, $attrValue, $storeId = null ){
        
        $attrSortMethod = 'DESC';
        $select = $this->_read->select()
            ->from($this->getMainTable())
            ->where($attrName . ' < ?', $attrValue)
            ->order($attrName . ' ' . $attrSortMethod)
            ->limit(1);
        
        if(!!$storeId){
            $select->where('store_id=?', $storeId);
        }
        
        $rowData = $this->_read->fetchRow($select);
        
        if(!$rowData){
            $rowData = array();    
        }
        
        return $rowData;
    }
    
    public function sortByAttribute($attrName, $attrSortMethod, $storeId = null){

        //Alowed Attribute should only be int or date, sort method should only within 'DESC' and 'ASC'
        $select = $this->_read->select()
            ->from($this->getMainTable())
            ->order($attrName . ' ' . $attrSortMethod);
            
        if(!!$storeId){
            $select->where('store_id=?', $storeId);
        }
        
        $rowData = $this->_read->fetchCol($select, 'id');
        
        if(!$rowData){
            $rowData = array();    
        }
        
        return $rowData;
    }
    
    public function checkEventProduct($categoryId) {        
        $select = $this->_read->select()
            ->from('catalog_category_product')
            ->where('category_id=?', $categoryId)
            ->limit(1);         
        $rowData = $this->_read->fetchRow($select);
        $result = false;
        if($rowData){
            $result = true;    
        }
        return $result;
    }

 /**
 * //Harapartners
 * public function sortByAttribute($attrName, $attrSortMethod, $storeId = null){

        //Alowed Attribute should only be int or date, sort method should only within 'DESC' and 'ASC'
        $select = $this->_read->select()
            ->from($this->getMainTable())
            ->order($attrName . ' ' . $attrSortMethod);
            
        if(!!$storeId){
            $select->where('store_id=?', $storeId);
        }
        
        $rowData = $this->_read->fetchCol($select, 'id');
        
        if(!$rowData){
            $rowData = array();    
        }
        
        return $rowData;
    }
 */
}