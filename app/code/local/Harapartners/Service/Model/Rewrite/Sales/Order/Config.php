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

class Harapartners_Service_Model_Rewrite_Sales_Order_Config extends Mage_Sales_Model_Order_Config {
    
    public function getStateStatuses($state, $addLabels = true) {
        $key = $state . $addLabels;
        if (isset($this->_stateStatuses[$key])) {
            return $this->_stateStatuses[$key];
        }
        $statuses = array();
        if (empty($state) || !is_array($state)) {
            $state = array($state);
        }
        foreach ($state as $_state) {
            if ($stateNode = $this->_getState($_state)) {
                $collection = Mage::getResourceModel('sales/order_status_collection')
                    //->addStateFilter($_state)
                    ->orderByLabel();
                foreach ($collection as $status) {
                    $code = $status->getStatus();
                    //Harapartners, Jun, Force ignore ogone, that status is by default in the DB
                    $pos = strpos($code,"ogone");
                    if($pos) {
                    	continue;
                    }
                    if ($addLabels) {
                        $statuses[$code] = $status->getStoreLabel();
                    } else {
                        $statuses[] = $code;
                    }
                }
            }
        }
        $this->_stateStatuses[$key] = $statuses;
        return $statuses;
    }

}