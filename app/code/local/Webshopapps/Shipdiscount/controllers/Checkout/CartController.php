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
 * @package    Mage_Checkout
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Webshopapps
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
/**
 * Shopping cart controller
 */
require_once 'Mage/Checkout/controllers/CartController.php';
class Webshopapps_Shipdiscount_Checkout_CartController extends Mage_Checkout_CartController
{  
    
    public function couponPostAction()
        {
            /**
             * No reason continue with empty shopping cart
             */
            if (!$this->_getCart()->getQuote()->getItemsCount()) {
                $this->_goBack();
                return;
            }
    
            $couponCode = (string) $this->getRequest()->getParam('coupon_code');
            if ($this->getRequest()->getParam('remove') == 1) {
                $couponCode = '';
            }
            $oldCouponCode = $this->_getQuote()->getCouponCode();
    
            if (!strlen($couponCode) && !strlen($oldCouponCode)) {
                $this->_goBack();
                return;
            }
    
            try {
                $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
                $this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
                    ->collectTotals()
                    ->save();
                    
                $this->_getQuote()->getShippingAddress()->setCouponCode($couponCode);
				$this->_getQuote()->setCouponCode($couponCode)
            		->save();
    
                if ($couponCode) {
                    if ($couponCode == $this->_getQuote()->getCouponCode()) {
                        $this->_getSession()->addSuccess(
                            $this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode))
                        );
                    }
                    else {
                        $this->_getSession()->addError(
                            $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode))
                        );
                    }
                } else {
                    $this->_getSession()->addSuccess($this->__('Coupon code was canceled.'));
                }
    
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($this->__('Cannot apply the coupon code.'));
            }
    
            $this->_goBack();
    }
    
}