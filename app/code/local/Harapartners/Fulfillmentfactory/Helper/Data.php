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
    const CITY_CONSTRAINT = 20;
    const ADDRESS_CONSTRAINT = 30;
    
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
    /**
    * 
    */
    public function validateAddressForDC($validate, $data){

        if (empty($data)) return null;

        switch($validate) {
            case 'CITY':
                $restraint = self::CITY_CONSTRAINT;
                break;
            // case 'ADDRESS':
            //  $restraint = self::ADDRESS_CONSTRAINT;
            //  break;
        }

        $length = strlen($data);

        if ($length > $restraint) {
            //strip punctuations
            $punctuations = "#[.,']#";
            $data = preg_replace($punctuations, '', $data);
            //check the length again, if so trim last characters according to the difference
            $length = strlen($data);
            if ($length > $restraint) {
                $diff = $length - $restraint;
                $data = substr($data, 0, -$diff);
            }
        }

        return $data;      
    }

    /**
    * Removes bad characters that affect xml, currently it focuses on the ampersand (&) character
    */
    public function removeBadCharacters($value) {
        return preg_replace('/&/','and',$value);
    }

    /**
     * Calculate and return the actual available quantity for a product after
     * accounting for order items that already have stock allocated.
     *
     * @param string $sku The SKU of the product to get an allocated count for.
     *
     * @return int
     */
    public function getAllocatedCount($sku)
    {
        $model     = Mage::getSingleton('fulfillmentfactory/itemqueue');
        $select    = new Zend_Db_Select($model->getResource()->getReadConnection());
        $tableName = $model->getResource()->getMainTable();

        $statuses = array($model::STATUS_READY, $model::STATUS_PARTIAL);

        $select->from($tableName)
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns("SUM(fulfill_count+0)")
            ->where('sku = ?', $sku)
            ->where('status IN (?)', $statuses);

        $stmt   = $select->query();
        $result = $stmt->fetch(Zend_Db::FETCH_COLUMN);

        return (int) $result;
    }

    /**
     * Attempt to submit an order for fulfillment.
     * First check all child order items to ensure they are either in READY or
     * CANCELLED status.
     *
     * @param Mage_Sales_Model_Order|int $orderId
     *
     * @return bool TRUE when the order was submitted successfully.
     */
    public function submitOrderForFulfillment($orderId) {
        if ($orderId instanceof Mage_Sales_Model_Order) {
            $orderId = $orderId->getId();
        }

        // locate all order items (those that belong to the same order)
        // including itself
        $orderItems = Mage::getModel('fulfillmentfactory/itemqueue')->getCollection();
        $orderItems->addFieldToFilter('order_id', $orderId);

        // inspect each order item's status
        $orderReady = true;
        foreach ($orderItems as $item) {
            if (Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_READY != $item->getStatus() &&
                Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_CANCELLED != $item->getStatus()
            ) {
                $orderReady = false;
            }
        }

        // submit this order for fulfillment if all items were READY or CANCELLED
        if ($orderReady) {
            $order = Mage::getModel('sales/order')->load($orderId);
            $orderArray = array($order);

            Mage::getSingleton('fulfillmentfactory/service_dotcom')
                ->submitOrdersToFulfill($orderArray, true);

            return true;
        }

        return false;
    }
}
