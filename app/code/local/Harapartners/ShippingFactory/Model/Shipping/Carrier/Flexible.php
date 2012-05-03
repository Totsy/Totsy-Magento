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

class Harapartners_ShippingFactory_Model_Shipping_Carrier_Flexible
        extends Mage_Shipping_Model_Carrier_Abstract
        implements Mage_Shipping_Model_Carrier_Interface
{
    
    const USA_COUNTRY_ID = 'US';
    const PUERTORICO_COUNTRY_ID = 'PR';
    const GUAM_COUNTRY_ID = 'GU';
    const GUAM_REGION_CODE = 'GU';
    
    const FREE_SHIPPING_AFTER_REGISTRATION_TIME = 2592000; // 30 days
    
    // Cache the quotes
    protected static $_quotesCache = array();
    
    protected $_code = 'flexible';
    protected $_carrierName = 'flexible';
    
    /**
     *  Default UPS Cgi gateway
     */
    protected $_defaultCgiGatewayUrl = 'http://www.ups.com:80/using/services/rave/qcostcgi.cgi';
    
    /**
     *  Raw rate request data
     *  @var Varien_Object|null
     */
    protected $_rawRequest = null;
    
    /**
     * Result
     */
    protected $_result = null;
    
    /**
     * Free Shipping logic only apply to Totsy clients not Mamasource
     */
    public function shouldUseFreeShipping(){
        if(!!Mage::registry('split_order_force_free_shipping')){
            return true;
        }
        
        //Harapartners, Jun, Coupon logic change: coupon code is batch generated and send by email
//        $quote = Mage::getSingleton('checkout/session')->getQuote();
//        $customer = $quote->getCustomer();
//        $storeId = $customer->getStoreId();
//        $storeName = $customer->getStore()->getName();
//        if(!!$customer && !!$customer->getId() && ($storeId == 1 || $storeName == 'totsy')
//                && strtotime($customer->getData('created_at')) + self::FREE_SHIPPING_AFTER_REGISTRATION_TIME > strtotime(now())){
//            $address = $quote->getShippingAddress();
//            if(!!$address && !!$address->getId() && 
//                    !count($address->getCustomerOrderCollection())){
//                return true;
//            }
//        }
        return false;
    }
    
    public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        $freeBoxes = 0;
        $shippingPrice = 0;
        
        //HP Song --Start
        $defaultShippingPrice = $this->getConfigData('default_shipping_price');
        $hasDefaultShippingItem = false;
       
<<<<<<< HEAD
        if($this->shouldUseFreeShipping()){
            $shippingPrice = '0.00';
        //HP Song --End
        }elseif ($request->getAllItems()) {
=======
		if($this->shouldUseFreeShipping()){
	        $shippingPrice = '0.00';
	    //HP Song --End
	    }elseif(is_numeric(Mage::registry('order_import_shipping_amount'))){
	    	$shippingPrice = max(array(0.0, Mage::registry('order_import_shipping_amount')));
	    	
	    }elseif ($request->getAllItems()) {
>>>>>>> hp
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }
                if($item->getHasChildren()){
                    foreach($item->getChildren() as $child){
                        $childProduct = Mage::getModel('catalog/product')->load($child->getProductId());
                        if($this->_isFlatRate($childProduct)){
                            $qty = $item->getQty() * $child->getQty();
                            $childPrice = $childProduct->getShippingRate() ? $childProduct->getShippingRate(): $defaultShippingPrice; 
                            
                            if($childPrice > $defaultShippingPrice){
                                $shippingPrice += $childPrice * $qty;
                            }elseif(!$hasDefaultShippingItem){
                                $shippingPrice += $childPrice;
                                $hasDefaultShippingItem = true;
                            }
                        }elseif($this->_isFreeShipping($childProduct)){
                            $freeBoxes += $item->getQty() * $child->getQty();
                            
                        }elseif($this->_isDimensional($childProduct)){
                            $this->setRequest($request, $childProduct);
                            $result = $this->_getQuote();
                            $qty = $item->getQty() * $child->getQty();
                            $shippingPrice += $result * $qty;
                        }
                    }
                } elseif (! $item->getParentItem() && ! $item->getHasChildren()) {
                    $product = Mage::getModel('catalog/product')->load($item->getProductId());
                    if($this->_isFlatRate($product)){
                        $qty = $item->getQty();
                        $itemPrice = $product->getShippingRate() ? $product->getShippingRate() : $defaultShippingPrice;
                        
                        if($itemPrice > $defaultShippingPrice){
                            $shippingPrice += $itemPrice * $qty;
                        }elseif(!$hasDefaultShippingItem){
                            $shippingPrice += $itemPrice;
                            $hasDefaultShippingItem = true;
                        }
                    }elseif($this->_isFreeShipping($product)){
                        $freeBoxes += $item->getQty();
                    }elseif($this->_isDimensional($product)){
                        $this->setRequest($request, $product);
                        $result = $this->_getQuote();
                        $qty = $item->getQty();
                        $shippingPrice += $result * $qty;
                        
                    }
                }else{
                       $shippingPrice = false;
                }
            }
        }
        $this->setFreeBoxes($freeBoxes);
        $result = Mage::getModel('shipping/rate_result');
        $shippingPrice = $this->getFinalPriceWithHandlingFee($shippingPrice);

        if ($shippingPrice !== false)
        {
            foreach ($this->getAllowedMethods() as $methodCode => $methodName)
            {                
                $method = Mage::getModel('shipping/rate_result_method');    
                $method->setCarrier($this->_carrierName);
                $method->setCarrierTitle($this->getConfigData('title'));    
                $method->setMethod($methodCode);
                $method->setMethodTitle($methodName);    
                if ($request->getFreeShipping() === true 
                        || $request->getPackageQty() == $this->getFreeBoxes()) 
                {
                    $shippingPrice = '0.00';
                }
                $method->setPrice($shippingPrice);
                $method->setCost($shippingPrice);    
                $result->append($method);                
            }     
        }
        return $result;
    }

    public function getAllowedMethods()
    {
        return array($this->_carrierName=>$this->getConfigData('name'));
    }
    
    /* Check the Shipping Method -- Start*/
    protected function _isFreeShipping($product)
    {
        return $this->_getShippingMethod($product) == 'free_shipping';
    }
    
    protected function _isFlatRate($product)
    {
        return $this->_getShippingMethod($product) == 'fix_rate'; // 'Flat Rate'
    }
    
    protected function _isDimensional($product)
    {
        return $this->_getShippingMethod($product) == 'dimensional';
    }
    
    protected function _getShippingMethod($product)
    {
        $shippingMethod = $product->getAttributeText('shipping_method');
         if(!$shippingMethod){
             return 'fix_rate';
         }
         return str_replace(' ', '_', strtolower(trim($shippingMethod)));
    }
    /* Check Shipping Method --End */
    
    /**
     * Prepare and set request 
     * 
     * @param Mage_Shipping_Model_Rate_Request $request 
     * @param Mage_Catalog_Model_Product $product
     * @return Harapartners_ShippingFactory_Model_Shipping_Carrier_Flexible
     */
    public function setRequest($request, $product)
    {   $this->_rawRequest = 0;
        $r = new Varien_Object();
        $r->setLength($product->getShippingLength());
        $r->setWidth($product->getShippingWidth());
        $r->setHeight($product->getShippingHeight());
        $r->setAction(Mage::helper('shippingfactory')->getCode('action' , 'all'));
        $r->setProduct('GND' . $this->getCofigData('dest_type'));
        
        $destType = $this->getConfigData('dest_type');
        $r->setDestType(Mage::helper('shippingfactory')->getCode('dest_type',$destType));
        
        $origCountry = Mage::getStoreConfig(
            Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID,
            $request->getStoreId()
        );
        $r->setOrigCountry(Mage::getModel('directory/country')->load($origCountry)->getIso2Code());
        
        $origRegionCode = Mage::getStoreConfig(
                Mage_Shipping_Model_Shipping::XML_PATH_STORE_REGION_ID,
                $request->getStoreId()
        );
        if (is_numeric($origRegionCode)) {
            $origRegionCode = Mage::getModel('directory/region')->load($origRegionCode)->getCode();
        }
        $r->setOrigRegionCode($origRegionCode);
        if ($request->getOrigPostcode()) {
            $r->setOrigPostal($request->getOrigPostcode());
        } else {
            $r->setOrigPostal(Mage::getStoreConfig(
                Mage_Shipping_Model_Shipping::XML_PATH_STORE_ZIP,
                $request->getStoreId()
            ));
        }

        if ($request->getOrigCity()) {
            $r->setOrigCity($request->getOrigCity());
        } else {
            $r->setOrigCity(Mage::getStoreConfig(
                Mage_Shipping_Model_Shipping::XML_PATH_STORE_CITY,
                $request->getStoreId()
            ));
        }


        if ($request->getDestCountryId()) {
            $destCountry = $request->getDestCountryId();
        } else {
            $destCountry = self::USA_COUNTRY_ID;
        }

        //for UPS, puero rico state for US will assume as puerto rico country
        if ($destCountry == self::USA_COUNTRY_ID
            && ($request->getDestPostcode()=='00912' || $request->getDestRegionCode()==self::PUERTORICO_COUNTRY_ID)
        ) {
            $destCountry = self::PUERTORICO_COUNTRY_ID;
        }

        // For UPS, Guam state of the USA will be represented by Guam country
        if ($destCountry == self::USA_COUNTRY_ID && $request->getDestRegionCode() == self::GUAM_REGION_CODE) {
            $destCountry = self::GUAM_COUNTRY_ID;
        }

        $r->setDestCountry(Mage::getModel('directory/country')->load($destCountry)->getIso2Code());

        $r->setDestRegionCode($request->getDestRegionCode());

        if ($request->getDestPostcode()) {
            $r->setDestPostal($request->getDestPostcode());
        } else {

        }
        $weight = $product->getWeight();
        //$weight = $this->getTotalNumOfBoxes($request->getPackageWeight());
        $r->setWeight($weight);
        
        if ($request->getUpsUnitMeasure()) {
            $unit = $request->getUpsUnitMeasure();
        } else {
            $unit = $this->getConfigData('unit_of_measure');
        }
        $r->setUnitMeasure($unit);

        $r->setIsReturn($request->getIsReturn());
        
        $this->_rawRequest = $r;
        
        return $this;
    }
    
    protected function _getQuote()
    {
        $r = $this->_rawRequest;
        $params = array(
            'accept_UPS_license_agreement'        =>    'yes',
            '10_action'                            =>  $r->getAction(),
            '13_product'                        =>    $r->getProduct(),
            '14_origCountry'                    =>    $r->getOrigCountry(),
            '15_origPostal'                        =>     $r->getOrigPostal(),
            '19_destPostal'                      =>     Mage_Usa_Model_Shipping_Carrier_Abstract::USA_COUNTRY_ID == $r->getDestCountry() ?
                                                    substr($r->getDestPostal(), 0, 5) :
                                                    $r->getDestPostal(),
            '22_destCountry'                     =>     $r->getDestCountry(),
            '23_weight'                          =>     $r->getWeight(),
            '25_length'                            =>    $r->getLength(),
            '26_width'                            =>     $r->getWidth(),
            '27_height'                            =>    $r->getHeight(),
            'weight_std'                        => strtolower($r->getUnitMeasure()),
            
        );
        $responseBody = $this->_getCachedQuotes($params);
        if($responseBody === null){
            $debugData = array('request' => $params);
            try{
                $url = $this->getConfigData('ups_gateway_url');
                if(! $url){
                    $url = $this->_defaultCgiGatewayUrl;
                }
                $client = new Zend_Http_Client();
                $client->setUri($url);
                $client->setParameterGet($params);
                $response = $client->request();
                $responseBody = $response->getBody();
                $debugData['result'] = $responseBody;
                $this->_setCachedQuotes($params, $responseBody);
            }catch(Exception $e){
                $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
                $responseBody = '';
            }
            $this->_debug($debugData);
        }
        return $this->_parseCgiResponse($responseBody);
    }
    
    protected function _parseCgiResponse($response)
    {
        $costArr = array();
        $priceArr = array();
        $errorTitle = Mage::helper('shippingfactory')->__('Unknown error');
        if (strlen(trim($response))>0) {
            $rRows = explode("\n", $response);
            $allowedMethods = explode(",", $this->getConfigData('allowed_methods'));
            foreach ($rRows as $rRow) {
                $r = explode('%', $rRow);
                switch (substr($r[0],-1)) {
                    case 3: case 4:
                        if (in_array($r[1], $allowedMethods)) {
                            $responsePrice = Mage::app()->getLocale()->getNumber($r[8]);
                            $costArr[$r[1]] = $responsePrice;
                            $priceArr[$r[1]] = $this->getMethodPrice($responsePrice, $r[1]);
                        }
                        break;
                    case 5:
                        $errorTitle = $r[1];
                        break;
                    case 6:
                        if (in_array($r[3], $allowedMethods)) {
                            $responsePrice = Mage::app()->getLocale()->getNumber($r[10]);
                            $costArr[$r[3]] = $responsePrice;
                            $priceArr[$r[3]] = $this->getMethodPrice($responsePrice, $r[3]);
                        }
                        break;
                }
            }
            asort($priceArr);
        }

        $result = Mage::getModel('shipping/rate_result');
        $defaults = $this->getDefaults();
        if (empty($priceArr) || ! isset($priceArr['GND'])) {
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier('ups');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        } else {
            
            if(isset($priceArr['GND'])){
                return $priceArr['GND'];
            }
//            foreach ($priceArr as $method=>$price) {
//                return $price;
//                $rate = Mage::getModel('shipping/rate_result_method');
//                $rate->setCarrier('ups');
//                $rate->setCarrierTitle($this->getConfigData('title'));
//                $rate->setMethod($method);
//                $method_arr = Mage::helper('shippingfactory')->getCode('method', $method);
//                $rate->setMethodTitle(Mage::helper('shippingfactory')->__($method_arr));
//                $rate->setCost($costArr[$method]);
//                $rate->setPrice($price);
//                $result->append($rate);
//            }
        }
//
//        return $result;
    }
    
    /**
     * Get configuration data of carrier
     *
     * @param string $type
     * @param string $code
     * @return array|bool
     */
    
    public function getTracking($trackings)
    {
        $return = array();

        if (!is_array($trackings)) {
            $trackings = array($trackings);
        }
        $this->_getCgiTracking($trackings);

        return $this->_result;
    }
    
    protected function _getCgiTracking($trackings)
    {
        //ups no longer support tracking for data streaming version
        //so we can only reply the popup window to ups.
        $result = Mage::getModel('shipping/tracking_result');
        $defaults = $this->getDefaults();
        foreach($trackings as $tracking){
            $status = Mage::getModel('shipping/tracking_result_status');
            $status->setCarrier('flexible');
            $status->setCarrierTitle($this->getConfigData('fexible'));
            $status->setTracking($tracking);
            $status->setPopup(1);
            $status->setUrl("http://wwwapps.ups.com/WebTracking/processInputRequest?HTMLVersion=5.0&error_carried=true"
                . "&tracknums_displayed=5&TypeOfInquiryNumber=T&loc=en_US&InquiryNumber1=$tracking"
                . "&AgreeToTermsAndConditions=yes"
            );
            $result->append($status);
        }

        $this->_result = $result;
        return $result;
    }
    
    public function getTrackingInfo($tracking)
    {
        $info = array();

        $result = $this->getTracking($tracking);

        if($result instanceof Mage_Shipping_Model_Tracking_Result){
            if ($trackings = $result->getAllTrackings()) {
                return $trackings[0];
            }
        }
        elseif (is_string($result) && !empty($result)) {
            return $result;
        }

        return false;
    }

    /**
     * Check if carrier has shipping tracking option available
     * All Mage_Usa carriers have shipping tracking option available
     *
     * @return boolean
     */
    public function isTrackingAvailable()
    {
        return true;
    }
    
    /**
     * Returns cache key for some request to carrier quotes service
     *
     * @param string|array $requestParams
     * @return string
     */
    protected function _getQuotesCacheKey($requestParams)
    {
        if (is_array($requestParams)) {
            $requestParams = implode(',', array_merge(
                array($this->getCarrierCode()),
                array_keys($requestParams),
                $requestParams)
            );
        }
        return crc32($requestParams);
    }

    /**
     * Checks whether some request to rates have already been done, so we have cache for it
     * Used to reduce number of same requests done to carrier service during one session
     *
     * Returns cached response or null
     *
     * @param string|array $requestParams
     * @return null|string
     */
    protected function _getCachedQuotes($requestParams)
    {
        $key = $this->_getQuotesCacheKey($requestParams);
        return isset(self::$_quotesCache[$key]) ? self::$_quotesCache[$key] : null;
    }

    /**
     * Sets received carrier quotes to cache
     *
     * @param string|array $requestParams
     * @param string $response
     * @return  Harapartners_ShippingFactory_Model_Shipping_Carrier_Flexible
     */
    protected function _setCachedQuotes($requestParams, $response)
    {
        $key = $this->_getQuotesCacheKey($requestParams);
        self::$_quotesCache[$key] = $response;
        return $this;
    }
}