<<<<<<< HEAD
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
        $value = preg_replace('/&/','and',$value);
        $value = preg_replace('/[^a-zA-Z0-9\s-_]/',"",$value);
        return $value;
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

        $statuses = array(
            Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_READY,
            Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_PARTIAL,
            Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_SUBMITTED
        );

        $select->from($tableName)
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns("SUM(fulfill_count+0)")
            ->where('sku = ?', $sku)
            ->where('status IN (?)', $statuses);

        $stmt   = $select->query();
        $result = $stmt->fetch(Zend_Db::FETCH_COLUMN);

        return (int) $result;
    }

    public function removeAccentsFromAddress($address)
    {
        $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
        $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
        foreach($address->getData() as $key => $data) {
            $address->setData($key, str_replace($a, $b, $data));
        }
        return $address;
    }

    /**
     * For ItemsQueue linked with the order, switch status to pending.
     */
    public function makeOrderReadyToBeProcessed($order) {
        if($order->getStatus() == Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_PAYMENT_FAILED) {
            $order->setStatus('pending')->save();
            $collection = Mage::getModel('fulfillmentfactory/itemqueue')->getCollection()->loadByOrderId($order->getId());
            foreach($collection as $itemqueue) {
                $itemqueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_PENDING)
                    ->save();
            }
        }
    }
}
=======
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
        $value = preg_replace('/&/','and',$value);
        $value = preg_replace('/[^a-zA-Z0-9\s-_]/',"",$value);
        return $value;
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

        $statuses = array(
            Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_READY,
            Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_PARTIAL,
            Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_SUBMITTED
        );

        $select->from($tableName)
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns("SUM(fulfill_count+0)")
            ->where('sku = ?', $sku)
            ->where('status IN (?)', $statuses);

        $stmt   = $select->query();
        $result = $stmt->fetch(Zend_Db::FETCH_COLUMN);

        return (int) $result;
    }

    public function removeAccentsFromAddress($address)
    {
        $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
        $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
        foreach($address->getData() as $key => $data) {
            $address->setData($key, str_replace($a, $b, $data));
        }
        return $address;
    }
    /**
     * Gets the fulfillment types as an array for select list
     * @return array
     */
    public function getAllFulfillmentTypesArray() {
        $_types = Mage::getStoreConfig ( 'fulfillmentfactory_options/general/fulfillment_types' );
        $types = explode(',', $_types);
        $returnArray = array();
        foreach ($types as $type) {
            $returnArray[$type] = $type;
        }
        return $returnArray;
    }
}
>>>>>>> feature/paypalAndPrivateLabel
