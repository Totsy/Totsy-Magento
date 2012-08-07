<?php
/**
 * @category    Totsy
 * @package     Totsy_Checkout
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

require_once "Mage/Checkout/controllers/CartController.php";

class Totsy_Checkout_CartController extends Mage_Checkout_CartController
{
    /**
     * Harapartners, yang, START
     * For cart timer
     *
     * @return int
     */
    protected function _getCurrentTime()
    {
        $defaultTimezone = date_default_timezone_get();
        $mageTimezone = Mage::getStoreConfig(
            Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE
        );
        date_default_timezone_set($mageTimezone);
        $timer = now();
        date_default_timezone_set($defaultTimezone);

        return strtotime($timer);
    }

    /**
     * Shopping cart display action.
     *
     * @return void
     */
    public function indexAction()
    {
        $cart = $this->_getCart();

        if ($cart->getQuote()->getItemsCount()) {
            $cart->init();

            // Harapartners, Jun
            // Additional handling in case Sailthru or SpeedTax lost connection
            try {
                $cart->save();
            } catch(Exception $e) {
                $this->_getSession()->addError($this->__($e->getMessage()));
            }

            if (!$this->_getQuote()->validateMinimumAmount()) {
                $cart->getCheckoutSession()->addNotice(
                    Mage::getStoreConfig('sales/minimum_order/description')
                );
            }
        }

        // Compose array of messages to add
        $messages = array();
        foreach ($cart->getQuote()->getMessages() as $message) {
            if ($message) {
                $messages[] = $message;
            }
        }
        $cart->getCheckoutSession()->addUniqueMessages($messages);

        /**
         * if customer enters shopping cart we should mark quote
         * as modified bc he can has checkout page in another window.
         */
        $this->_getSession()->setCartWasUpdated(true);

        Varien_Profiler::start(__METHOD__ . 'cart_display');
        $this
            ->loadLayout()
            ->_initLayoutMessages('checkout/session')
            ->_initLayoutMessages('catalog/session')
            ->getLayout()->getBlock('head')->setTitle($this->__('Shopping Cart'));
        $this->renderLayout();
        Varien_Profiler::stop(__METHOD__ . 'cart_display');
    }

    /**
     * Add product to shopping cart action.
     *
     * @return void
     */
    public function addAction()
    {
    
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();
        
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
                $this->_goBack();
                return;
            }

            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            // Harapartners, yang
            // For header floating cart logic
            $this->_getSession()->setCartUpdatedFlag(true);

            // Harapartners, yang
            // Set new cart timer
            $this->_getSession()->setCountDownTimer($this->_getCurrentTime());

            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent(
                'checkout_cart_add_product_complete',
                array(
                    'product' => $product,
                    'request' => $this->getRequest(),
                    'response' => $this->getResponse()
                )
            );

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $html = '%s was added to your shopping cart.<strong class="non-mobile-hide"> <a href="'.Mage::helper('checkout/cart')->getCartUrl().'" class="">Checkout</a></strong>';
                    $message = $this->__(
                        $html,
                        Mage::helper('core')->htmlEscape($product->getName())
                    );
                    $this->_getSession()->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError($message);
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(
                    Mage::helper('checkout/cart')->getCartUrl()
                );
            }
        } catch (Exception $e) {
            $this->_getSession()->addException(
                $e,
                $this->__('Cannot add the item to shopping cart.')
            );
            Mage::logException($e);
            $this->_goBack();
        }
    }

    /**
     * Update shoping cart data action.
     *
     * @return void
     */
    public function updatePostAction()
    {
        parent::updatePostAction();

        // Harapartners, yang
        // Set new cart timer
        $this->_getSession()->setCartWasUpdated(true)
            ->setCountDownTimer($this->_getCurrentTime());
    }
}
