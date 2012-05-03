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
class Harapartners_Dropshipfactory_Model_Service extends Mage_Core_Model_Abstract {
    
    /**
     * get folder path for storing export file
     *
     * @return string folder path
     */
    public function getExportFolderPath() {
        $path = Mage::getBaseDir('var') . DS . 'dropship';
        
        return $path;
    }
    
    /**
     * get full .csv file path
     *
     * @return string file path
     */
    public function getExportCSVFilePath() {
        //$filepath = $this->getExportFolderPath() . DS . 'dropship_' . date('YmdHi'). '.csv';
        $filepath = 'dropship_' . date('YmdHis'). '.csv';
        
        return $filepath;
    }
    
    /**
     * get folder pathes for each vendor
     *
     * @param string $vendor
     * @return associated array
     */
    public function getExportFolderPathByVendor($vendor) {
        $VENDOR_FILE_PATH = array (
            'vendor 1' => '/tmp/'
        );
        
        return $VENDOR_FILE_PATH[$vendor];
    }
    
    /**
     * get header of export CSV file
     *
     * @return array
     */
    public function getCSVHeader() {
        $header = array (
                    'ITEM_ID',
                    'PRODUCT_NAME',
                    'PRODUCT_SKU',
                    'ORDER_NO',
                    'ORDER_DATE',
                    'VENDOR',
                    'CUSTOMER_NAME',
                    'CUSTOMER_ADDR',
                    'CUSTOMER_CITY',
                    'CUSTOMER_STATE',
                    'CUSTOMER_ZIP',
                    'CUSTOMER_PHONE',
                    'CUSTOMER_EMAIL',
                    'SHIPPING_NAME',
                    'SHIPPING_ADDR',
                    'SHIPPING_CITY',
                    'SHIPPING_STATE',
                    'SHIPPING_ZIP',
                    'PRICE',
                    'TAX',
                    'QTY',
                    'SHIPPING_METHOD',
                    'SHIPPING_AMOUNT'
        );
        
        return $header;
    }
    
    /**
     * Import tracking information
     *
     * @param string $orderId
     * @param string $carrier
     * @param string $trackingNumber
     */
    public function _importTracking($orderId, $carrier, $trackingNumber) {
        if(empty($trackingNumber)) {
            return false;
        }
                    
        //check if tracking number exists
        $queryTrackingResult = Mage::getModel('sales/order_shipment_track')->getCollection()
                                    ->addFieldToFilter('track_number', $trackingNumber);
                                    
        if(empty($queryTrackingResult) || (count($queryTrackingResult) <= 0)) {
            $title = $carrier;
            $trackingData = array (
                'carrier_code'=>$carrier,
                'title'=>$title,
                'number'=>$trackingNumber
            );
            
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            
            if(!!$order && !!$order->getId()) {
                $shipmentCollection = $order->getShipmentsCollection();
                $shipment = Mage::getModel('sales/order_shipment');
                
                if(!empty($shipmentCollection) && (count($shipmentCollection) > 0)) {
                    $shipment = $shipmentCollection->getFirstItem();    //get first item
                }
                else {
                    $itemQtyArray = array();
                    foreach ($order->getAllItems() as $item){
                        $itemQtyArray[$item->getData('item_id')] = (int)$item->getData('qty_ordered'); 
                    }
                    
                    $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($itemQtyArray);
                }
                
                $track = Mage::getModel('sales/order_shipment_track')->addData($trackingData);
                $shipment->addTrack($track);
                
                $order->setIsInProcess(true);
                $shipment->sendEmail(true);
                $shipment->setEmailSent(true);
                
                $shipment->save();
                $order->save();
            }
        }
        
        return true;
    }
    
    /**
     * import tracking information from csv file
     *
     * @param string $csv_file_name
     */
    public function importTrackingFromCSV($csv_file_name) {
        if (($handle = fopen($csv_file_name, "r")) !== FALSE) {
            $row = 0;
            while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
                if(count($data) >= 3) {                    
                    $this->_importTracking($data[0], $data[1], $data[2]);
                }
            }
            
            fclose($handle);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * import tracking number from XML
     *
     * @param Object $xml
     */
    public function importTrackingFromXML($xml) {
        foreach($xml->children() as $item) {
            $orderId = (string)$item[0]->OrderNo;
            $carrier = strtolower((string)$item[0]->ShipAgent);//'ups';
            $trackingNumber = (string)$item[0]->TrackingNo;
            
            $this->_importTracking($orderId, $carrier, $trackingNumber);
        }
    }
}