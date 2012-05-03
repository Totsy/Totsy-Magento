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
class Harapartners_Fulfillmentfactory_Helper_Data extends Mage_Core_Helper_Abstract{
    const ORDER_STATUS_PROCESSING_FULFILLMENT = 'processing_fulfillment';
    const ORDER_STATUS_PAYMENT_FAILED = 'payment_failed';
    const ORDER_STATUS_FULFILLMENT_FAILED = 'fulfillment_failed';
    const ORDER_STATUS_FULFILLMENT_AGING = 'fulfillment_aging';
    const ORDER_STATUS_SHIPMENT_AGING = 'shipment_aging';
    
    /**
     * get 2-letter code for states
     *
     * @param string $stateName
     * @param string $countryCode
     * @return string state code
     */
    public function getStateCodeByFullName($stateName, $countryCode) {
        $stateCode = $stateName;
        
        $stateObj = Mage::getModel('directory/region')->loadByName($stateName, $countryCode);
        
        if(!empty($stateObj)) {
            $stateCode = $stateObj->getCode();
        }
        
        return $stateCode;
    }
    
    /**
     * Auxiliary function for pushing order into array, which can avoid duplicate of orders.
     *
     * @param Array $orderArray
     * @param Object $order
     */
    public function _pushUniqueOrderIntoArray(&$orderArray, $order) {
        if(empty($order) || !$order->getId()) {
            return;
        }
        
        $shouldBeAdded = true;
        
        foreach($orderArray as $existOrder) {
            if($existOrder->getId() == $order->getId()) {
                $shouldBeAdded = false;
                break;
            }
        }
        
        if($shouldBeAdded) {
            $orderArray[] = $order;
        }
    }
    
    /*
     * get array list of Status (for dropdown list)
     */
    public function getItemqueueStatusDropdownOptionList() {
        return array(
            array(
                'label' => 'Pending',
                'value' => Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_PENDING
            ),
            array(
                'label' => 'Partial filled',
                'value' => Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_PARTIAL
            ),
            array(
                'label' => 'Ready',
                'value' => Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_READY
            ),
            array(
                'label' => 'Processing',
                'value' => Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_PROCESSING
            ),
            array(
                'label' => 'Suspended',
                'value' => Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_SUSPENDED
            ),
            array(
                'label' => 'Submitted',
                'value' => Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_SUBMITTED
            ),
            array(
                'label' => 'Complete',
                'value' => Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_CLOSED
            ),
            array(
                'label' => 'Cancelled',
                'value' => Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_CANCELLED
            ),
        );
    }
    
    /*
     * get option array list of Status (for grid list)
     */
    public function getItemqueueStatusGridOptionList() {
        return array(
            Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_PENDING => 'Pending',
            Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_PARTIAL => 'Partial filled',
            Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_READY => 'Ready',
            Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_PROCESSING => 'Processing',
            Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_SUSPENDED => 'Suspended',
            Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_SUBMITTED => 'Submitted',
            Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_CLOSED => 'Complete',
            Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_CANCELLED => 'Cancelled'
        );
    }
}