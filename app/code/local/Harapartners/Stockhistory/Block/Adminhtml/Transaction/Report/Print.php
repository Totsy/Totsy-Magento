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

class Harapartners_Stockhistory_Block_Adminhtml_Transaction_Report_Print extends Harapartners_Stockhistory_Block_Adminhtml_Transaction_Report_Abstract {
    
    const BUSINESS_DAYS_SHIP_BY = 2;
    const BUSINESS_DAYS_IN_HOUSE = 7;
    
    public function __construct() {
        parent::__construct();
        $this->setTemplate('stockhistory/print.phtml');
    }
    
    public function getPoNumber(){
        return $this->getPoObject()->generatePoNumber();
    }
    
	public function getPoCategoryName(){
        $categoryId = $this->getPoObject()->getCategoryId();
        
        $category = Mage::getModel('catalog/category')->load($categoryId);
		return $category->getName();
    }
    
    public function getPoDate(){
        return date('m-d-Y', $this->_getCurrentTime());
    }
    
    public function getShipByDate(){
        return date('m-d-Y', $this->_getWorkDayTime($this->_getCurrentTime(), self::BUSINESS_DAYS_SHIP_BY));
    }
    
    public function getInHouseDate(){
        return date('m-d-Y', $this->_getWorkDayTime($this->_getCurrentTime(), self::BUSINESS_DAYS_IN_HOUSE));
    }
    
    public function getAuthorizationName(){
        return Mage::getSingleton('admin/session')->getUser()->getName();
    }
    
	public function getVendorName(){
        return $this->getVendorObj()->getData('vendor_name');
    }
    
	public function getContactPerson(){
        return $this->getVendorObj()->getData('contact_person');
    }
    
	public function getTelephone(){
        return $this->getVendorObj()->getData('telephone');
    }
    
    public function getVendorAddress(){
        $address = $this->getVendorObj()->getData('address');
        $address = str_ireplace("\n", "<br/>", $address);
        return $address;
    }
    
	public function getPaymentTerms(){
        $terms = $this->getVendorObj()->getData('payment_terms');
        $terms = str_ireplace("\n", "<br/>", $terms);
        return $terms;
    }
    
	public function getBankingInfo(){
        $terms = $this->getVendorObj()->getData('banking_info');
        $terms = str_ireplace("\n", "<br/>", $terms);
        return $terms;
    }
    
    public function getVendorEmailList(){
        $emailList = $this->getVendorObj()->getData('email_list');
        return implode('<br/>', explode(',', $emailList));
    }
    
    // ========== Utilities ========== //
    protected function _getCurrentTime(){
           $defaultTimezone = date_default_timezone_get();
        $mageTimezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);            
        date_default_timezone_set($mageTimezone);
        $timer = now();
        date_default_timezone_set($defaultTimezone);
        return strtotime($timer);
    }
    
    protected function _getWorkDayTime($time, $dayDelta = 1){
        $defaultTimezone = date_default_timezone_get();
        $mageTimezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);            
        date_default_timezone_set($mageTimezone);
        for($dayCount = 0; $dayCount < $dayDelta; $dayCount ++){
            $time = $this->_getNextWorkDayTime($time);
        }
        date_default_timezone_set($defaultTimezone);
        return $time;
    }
    
    protected function _getNextWorkDayTime($date) {
        $time = is_string($date) ? strtotime($date) : (is_int($date) ? $date : time());
        $time += 86400;
        $y = date('Y', $time);
        
        // Calculate federal holidays by current Year
        $holidays = array();
        // month/day (jan 1st). iteration/wday/month (3rd monday in january)
        $hdata = array('1/1'/*newyr*/, '7/4'/*jul4*/, '11/11'/*vet*/, '12/25'/*xmas*/, '3/1/1'/*mlk*/, '3/1/2'/*pres*/, '5/1/5'/*memo*/, '1/1/9'/*labor*/, '2/1/10'/*col*/, '4/4/11'/*thanks*/);
        foreach ($hdata as $h1) {
            $h = explode('/', $h1);
            if (sizeof($h) == 2) { // by date
                $htime = mktime(0, 0, 0, $h[0], $h[1], $y); // time of holiday
                $w = date('w', $htime); // get weekday of holiday
                $htime += $w == 0 ? 86400 : ($w == 6 ? -86400 : 0); // if weekend, adjust
            } else { // by weekday
                $htime = mktime(0, 0, 0, $h[2], 1, $y); // get 1st day of month
                $w = date('w', $htime); // weekday of first day of month
                $d = 1 + ($h[1]-$w+7) % 7; // get to the 1st weekday
                for ($t = $htime, $i = 1; $i <= $h[0]; $i++, $d += 7) { // iterate to nth weekday
                     $t = mktime(0, 0, 0, $h[2], $d, $y); // get next weekday
                     if (date('n', $t)>$h[2]) break; // check that it's still in the same month
                     $htime = $t; // valid
                }
            }
            $holidays[] = $htime; // save the holiday
        }
        
        for ($i = 0; $i<5; $i++, $time += 86400) { // 5 days should be enough to get to workday
            if (in_array(date('w', $time), array(0, 6))) continue; // skip weekends
            foreach ($holidays as $h) { // iterate through holidays
                if ($time >= $h && $time<$h+86400) continue 2; // skip holidays
            }
            break; // found the workday
        }
        return $time;
    } 
    
}