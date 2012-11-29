<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Usa
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract USA shipping carrier model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
/**
 * Magento Webshopapps Module
 *
 * @category   Webshopapps
 * @package    Webshopapps Wsacommon
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    www.webshopapps.com/license/license.txt
 * @author     Karen Baker <sales@webshopapps.com>
*/

abstract class Webshopapps_Wsacommon_Model_Shipping_Carrier_Baseabstract extends Mage_Shipping_Model_Carrier_Abstract
{
    protected $_debug;
    
    protected $_request = null;

    protected $_result = null;

    protected $_rawRequest = null;
    protected $_modName   = 'none';
    
   	protected $_code;
    
   	abstract protected  function _getQuotes();
 	abstract public function getCode($type, $code='');
    
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

  	public function getResult()
    {
       return $this->_result;
    }
    
 	/**
     * Enter description here...
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        } 
        if ($this->_modName == 'none') {
        	$this->_debug = $this->getConfigData('debug');
        } else {
        	$this->_debug = Mage::helper('wsalogger')->isDebug($this->_modName);
        }
        
        
        $this->setRequest($request);
        
        $this->_result = $this->_getQuotes();
        
        $this->_updateFreeMethodQuote($request);

        return $this->getResult(); 
    }
    
    
 	protected function _setFreeMethodRequest($freeMethod)
    {
    	$this->_rawRequest->setIgnoreFreeItems(true);
    }
    
    public function getAllowedMethods()
    {
        return array($_code=>$this->getConfigData('name'));
    }  
}
