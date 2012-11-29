<?php
/**
 * Magento Webshopapps Shipping Module
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
 * @category   Webshopapps
 * @package    Webshopapps_Productmatrix
 * @copyright  Copyright (c) 2009 Auction Maid (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt
 * @author     Karen Baker <enquiries@webshopapps.com>
*/
/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */

class Webshopapps_Shipdiscount_Model_Shipdiscount  extends Mage_Core_Model_Abstract {

	public function __construct()
    {
    	
    }
    
	public function getDiscountText($websiteId, $customerGroupId, $couponCode) {

		if (empty($couponCode) || $couponCode=="") { return;}
		$rules = Mage::getModel('salesRule/rule')
                ->getResourceCollection()
                ->setValidationFilter($websiteId, $customerGroupId, $couponCode)
                ->load();

       	foreach ($rules as $rule) {
       		if ($rule->getCode()==$couponCode && (
       			$rule->getSimpleAction()=="by_ship_percent" || $rule->getSimpleAction()=="by_ship_fixed" )) {
       			return $rule->getDescription();
       		}
       		
       	}          

	}
    
}