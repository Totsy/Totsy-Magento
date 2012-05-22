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

class Harapartners_Service_Model_Rewrite_Sales_Resource_Order extends Mage_Sales_Model_Resource_Order {
  
	//Overriding Mage_Sales_Model_Resource_Order_Abstract
    public function updateGridRecords($ids) {
        if ($this->_grid) {
            if (!is_array($ids)) {
                $ids = array($ids);
            }

            if ($this->_eventPrefix && $this->_eventObject) {
                $proxy = new Varien_Object();
                $proxy->setIds($ids)
                    ->setData($this->_eventObject, $this);

                Mage::dispatchEvent($this->_eventPrefix . '_update_grid_records', array('proxy' => $proxy));
                $ids = $proxy->getIds();
            }

            if (empty($ids)) { // If nothing to update
                return $this;
            }
            $columnsToSelect = array();
            $table = $this->getGridTable();
            $select = $this->getUpdateGridRecordsSelect($ids, $columnsToSelect);
            
            //Harapartners, Jun
            //INSERT... SELECT... ON DUPLICATE KEY UPDATE is known to create problems with master/slave sync, for Totsy, separate SELECT and INSERT
            //$this->_getWriteAdapter()->query($select->insertFromSelect($table, $columnsToSelect, true));
            $data = $this->_getWriteAdapter()->fetchRow($select->assemble());
            $this->_getWriteAdapter()->insertOnDuplicate($table, $data, $columnsToSelect);
        }

        return $this;
    }
    
}