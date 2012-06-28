<?php
/**
 * Created by JetBrains PhpStorm.
 * User: chris.davidowski
 * Date: 6/22/12
 * Time: 10:23 AM
 */

require_once  'Mage/Checkout/controllers/CartController.php';

class Totsy_Ajax_Checkout_CartController extends Mage_Checkout_CartController {
    public function addAction() {
        if(!Mage::getModel('rushcheckout/observer')->isValid(Mage::getSingleton('customer/session'))) {
            $response = array();
            $response['redirect'] = Mage::getModel('rushcheckout/observer')->getValidationUrl();
            Mage::getSingleton('customer/session')->setCheckLastValidationFlag(false);
            Mage::getModel('rushcheckout/observer')->setValidationRedirect(Mage::getSingleton('customer/session'),$this->_getRefererUrl());
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
            return;
        }
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();
        if($this->getRequest()->getParam('isAjax') == 1){
            $response = array();
            try {
                if (isset($params['qty'])) {
                    $filter = new Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                    );
                    $params['qty'] = $filter->filter($params['qty']);
                }

                $product = $this->_initProduct();
                $related = $this->getRequest()->getParam('related_product');

                /**
                 * Check product availability
                 */
                if (!$product) {
                    $response['status'] = 'ERROR';
                    $response['message'] = $this->__('Unable to find Product ID');
                }

                $cart->addProduct($product, $params);
                if (!empty($related)) {
                    $cart->addProductsByIds(explode(',', $related));
                }

                $cart->save();
                Mage::register('product',$product);

                $this->_getSession()->setCartWasUpdated(true);
                $this->_getSession()->setCartUpdatedFlag(true);        //Harapartners, yang, for header flotting cart logic
                $this->_getSession()->setCountDownTimer($this->_getCurrentTime());    //Harapartners, yang, set new cart timer

                /**
                 * @todo remove wishlist observer processAddToCart
                 */
                Mage::dispatchEvent('checkout_cart_add_product_complete',
                    array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
                );

                if (!$this->_getSession()->getNoCartRedirect(true)) {
                    if (!$cart->getQuote()->getHasError()){
                        $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->htmlEscape($product->getName()));
                        $response['status'] = 'SUCCESS';
                        $response['message'] = $message;
//New Code Here
                        $this->loadLayout();
                        $headerCart = $this->getLayout()->getBlock('header.cart')->toHtml();
//                        $productbox = $this->getLayout()->getBlock('product.box')->toHtml();
                        $response['headercart'] = $headerCart;
//                        $response['productbox'] = $productbox;
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $msg = "";
                if ($this->_getSession()->getUseNotice(true)) {
                    $msg = $e->getMessage();
                } else {
                    $messages = array_unique(explode("\n", $e->getMessage()));
                    foreach ($messages as $message) {
                        $msg .= $message.'<br/>';
                    }
                }

                $response['status'] = 'ERROR';
                $response['message'] = $msg;
            } catch (Exception $e) {
                $response['status'] = 'ERROR';
                $response['message'] = $this->__('Cannot add the item to shopping cart.');
                Mage::logException($e);
            }
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
            return;
        } else {
            return parent::addAction();
        }
    }
}