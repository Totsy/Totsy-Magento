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
 * @package    Mage_Shipping
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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

class Webshopapps_Wsacommon_Model_Shipping_Shipping extends Mage_Shipping_Model_Shipping
{

	
	/**
     * Retrieve all methods for supplied shipping data
     *
     * @todo make it ordered
     * @param Mage_Shipping_Model_Shipping_Method_Request $data
     * @return Mage_Shipping_Model_Shipping
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
    	
    	if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Dropship')) {
     		if (!Mage::registry('dropship_shipmodel')) {
				$model = Mage::getModel('dropship/shipping_shipping');
				Mage::register('dropship_shipmodel', $model);
			}
			Mage::registry('dropship_shipmodel')->resetResult();
			return Mage::registry('dropship_shipmodel')->collectRates($request);
	 	}
	 	
    	if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Wsamultiorigin')) {
     		if (!Mage::registry('wsamultiorigin_shipmodel')) {
				$model = Mage::getModel('wsamultiorigin/shipping_shipping');
				Mage::register('wsamultiorigin_shipmodel', $model);
			}
			Mage::registry('wsamultiorigin_shipmodel')->resetResult();
			return Mage::registry('wsamultiorigin_shipmodel')->collectRates($request);
	 	}
	 	
	 	if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Rgshipping')) {
     		if (!Mage::registry('rgshipping_shipmodel')) {
				$model = Mage::getModel('rgshipping/shipping_shipping');
				Mage::register('rgshipping_shipmodel', $model);
			}
			Mage::registry('rgshipping_shipmodel')->resetResult();
			return Mage::registry('rgshipping_shipmodel')->collectRates($request);
	 	}
	 	
    	if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Freightrate')) {
     		if (!Mage::registry('freightrate_shipmodel')) {
				$model = Mage::getModel('freightrate/shipping_shipping');
				Mage::register('freightrate_shipmodel', $model);
			}
			Mage::registry('freightrate_shipmodel')->resetResult();
			return Mage::registry('freightrate_shipmodel')->collectRates($request);
	 	}
	
    	
    	if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Shippingoverride2')) {
     		if (!Mage::registry('override2_shipmodel')) {
				$model = Mage::getModel('shippingoverride2/shipping_shipping');
				Mage::register('override2_shipmodel', $model);
			}
			Mage::registry('override2_shipmodel')->resetResult();
			return Mage::registry('override2_shipmodel')->collectRates($request);
	 	}
	
	 	
     	if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Conwayfreight')) {
     		if (!Mage::registry('conway_shipmodel')) {
				$model = Mage::getModel('conwayfreight/shipping_shipping');
				Mage::register('conway_shipmodel', $model);
			}
			Mage::registry('conway_shipmodel')->resetResult();
			return Mage::registry('conway_shipmodel')->collectRates($request);
	 	}

	 	
     	if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Abffreight')) {
     		if (!Mage::registry('abffreight_shipmodel')) {
				$model = Mage::getModel('abffreight/shipping_shipping');
				Mage::register('abffreight_shipmodel', $model);
			}
			Mage::registry('abffreight_shipmodel')->resetResult();
			return Mage::registry('abffreight_shipmodel')->collectRates($request);
	 	}
	 	
        if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Wsaupsfreight')) {
     		if (!Mage::registry('wsaupfreight_shipmodel')) {
				$model = Mage::getModel('wsaupsfreight/shipping_shipping');
				Mage::register('wsaupfreight_shipmodel', $model);
			}
			Mage::registry('wsaupfreight_shipmodel')->resetResult();
			return Mage::registry('wsaupfreight_shipmodel')->collectRates($request);
	 	}
	 	        	
     	if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Yrcfreight')) {
     		if (!Mage::registry('yrc_shipmodel')) {
				$model = Mage::getModel('yrcfreight/shipping_shipping');
				Mage::register('yrc_shipmodel', $model);
			}
			Mage::registry('yrc_shipmodel')->resetResult();
			return Mage::registry('yrc_shipmodel')->collectRates($request);
	 	}
	 	
    	if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Rlfreight')) {
     		if (!Mage::registry('rlfreight_shipmodel')) {
				$model = Mage::getModel('rlfreight/shipping_shipping');
				Mage::register('rlfreight_shipmodel', $model);
			}
			Mage::registry('rlfreight_shipmodel')->resetResult();
			return Mage::registry('rlfreight_shipmodel')->collectRates($request);
	 	}
	 	
	 	return parent::collectRates($request);
    }
    
	
	/**
	 * Overrides this method in core, and decides which extension to call
	 * Uses a hierarchy to decide on best extension
	 * @see app/code/core/Mage/Shipping/Model/Mage_Shipping_Model_Shipping::collectCarrierRates()
	 */
 	public function collectCarrierRates($carrierCode, $request)
 	{

 		// check to see if handling Product enabled
	 	if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Handlingproduct')) {
			if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Shipusa')) {
		 		return parent::collectCarrierRates($carrierCode,$request);
		 		
		 	} else {
		 		if (!Mage::registry('handlingproduct_shipmodel')) {
					$model = Mage::getModel('handlingproduct/shipping_shipping');
					Mage::register('handlingproduct_shipmodel', $model);
				}
				$model = Mage::registry('handlingproduct_shipmodel') ;
				$model->collectCarrierRates($carrierCode, $request);
				$this->_result=$model->getResult();
				return $model;
				
		 	}
		}
		
 		if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Handlingmatrix')) {
			if (!Mage::registry('handlingmatrix_shipmodel')) {
				$model = Mage::getModel('handlingmatrix/shipping_shipping');
				Mage::register('handlingmatrix_shipmodel', $model);
			}
			$model = Mage::registry('handlingmatrix_shipmodel');
			$model->collectCarrierRates($carrierCode, $request);
			$this->_result=$model->getResult();
			return $model;
		}
 	 	
        if (Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Insurance')) {
     		if (!Mage::registry('insurance_shipmodel')) {
				$model = Mage::getModel('insurance/shipping_shipping');
				Mage::register('insurance_shipmodel', $model);
			}
			$model = Mage::registry('insurance_shipmodel');
			$model->collectCarrierRates($carrierCode, $request);
			$this->_result=$model->getResult();
			return $model;
         }
		
		
	 	// default
	 	return parent::collectCarrierRates($carrierCode,$request);
	 	
	 }
	 
	
}