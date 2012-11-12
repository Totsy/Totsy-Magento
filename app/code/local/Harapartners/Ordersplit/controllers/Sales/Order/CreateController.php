<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ryan.street
 * Date: 11/7/12
 * Time: 10:58 AM
 * To change this template use File | Settings | File Templates.
 */

include_once 'Mage/Adminhtml/controllers/Sales/Order/CreateController.php';

class Harapartners_Ordersplit_Sales_Order_CreateController extends Mage_Adminhtml_Sales_Order_CreateController {

    /**
     * Process request data with additional logic for saving quote and creating order
     *
     * @param string $action
     * @return Mage_Adminhtml_Sales_Order_CreateController
     */
    protected function _processActionData($action = null)
    {
        /**
         * Saving order data
         */
        if ($data = $this->getRequest()->getPost('order')) {
            $this->_getOrderCreateModel()->importPostData($data);
        }

        /**
         * Initialize catalog rule data
         */
        $this->_getOrderCreateModel()->initRuleData();

        /**
         * init first billing address, need for virtual products
         */
        $this->_getOrderCreateModel()->getBillingAddress();

        /**
         * Flag for using billing address for shipping
         */
        if (!$this->_getOrderCreateModel()->getQuote()->isVirtual()) {
            $syncFlag = $this->getRequest()->getPost('shipping_as_billing');
            if (is_null($syncFlag)
                && $this->_getOrderCreateModel()->getShippingAddress()->getSameAsBilling()
                && is_null($this->_getOrderCreateModel()->getShippingMethod())
            ) {
                $this->_getOrderCreateModel()->setShippingAsBilling(1);
            } else {
                $this->_getOrderCreateModel()->setShippingAsBilling((int)$syncFlag);
            }
        }

        /**
         * Change shipping address flag
         */
        if (!$this->_getOrderCreateModel()->getQuote()->isVirtual() && $this->getRequest()->getPost('reset_shipping')) {
            $this->_getOrderCreateModel()->resetShippingMethod(true);
        }

        /**
         * Collecting shipping rates
         */
        if (!$this->_getOrderCreateModel()->getQuote()->isVirtual() &&
            $this->getRequest()->getPost('collect_shipping_rates')
        ) {
            $this->_getOrderCreateModel()->collectShippingRates();
        }


        /**
         * Apply mass changes from sidebar
         */
        if ($data = $this->getRequest()->getPost('sidebar')) {
            $this->_getOrderCreateModel()->applySidebarData($data);
        }

        /**
         * Adding product to quote from shopping cart, wishlist etc.
         */
        if ($productId = (int) $this->getRequest()->getPost('add_product')) {
            $this->_getOrderCreateModel()->addProduct($productId, $this->getRequest()->getPost());
        }

        /**
         * Adding products to quote from special grid
         */
        if ($this->getRequest()->has('item') && !$this->getRequest()->getPost('update_items') && !($action == 'save')) {
            $items = $this->getRequest()->getPost('item');

            $quoteItems = $this->_getQuote()->getAllItems();

            foreach($quoteItems as $key => $value) {
                $quoteProduct = Mage::getModel('catalog/product')->load($key);

                $fulfillmentTypes[$quoteProduct->getFulfillmentType()][] = $key;
            }

            foreach($items as $key=>$value) {
                $product = Mage::getModel('catalog/product')->load($key);
                $fulfillmentTypes[$product->getFulfillmentType()][] = $key;
            }

            if(count($fulfillmentTypes) > 1) {
                Mage::throwException('Multiple Fulfillment Types detected. You can only create orders with products of the same fulfillment type.');
            }

            $items = $this->_processFiles($items);
            $this->_getOrderCreateModel()->addProducts($items);
        }

        /**
         * Update quote items
         */
        if ($this->getRequest()->getPost('update_items')) {
            $items = $this->getRequest()->getPost('item', array());
            $items = $this->_processFiles($items);
            $this->_getOrderCreateModel()->updateQuoteItems($items);
        }

        /**
         * Remove quote item
         */
        $removeItemId = (int) $this->getRequest()->getPost('remove_item');
        $removeFrom = (string) $this->getRequest()->getPost('from');
        if ($removeItemId && $removeFrom) {
            $this->_getOrderCreateModel()->removeItem($removeItemId, $removeFrom);
        }

        /**
         * Move quote item
         */
        $moveItemId = (int) $this->getRequest()->getPost('move_item');
        $moveTo = (string) $this->getRequest()->getPost('to');
        if ($moveItemId && $moveTo) {
            $this->_getOrderCreateModel()->moveQuoteItem($moveItemId, $moveTo);
        }

        /*if ($paymentData = $this->getRequest()->getPost('payment')) {
            $this->_getOrderCreateModel()->setPaymentData($paymentData);
        }*/
        if ($paymentData = $this->getRequest()->getPost('payment')) {
            $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
        }

        $eventData = array(
            'order_create_model' => $this->_getOrderCreateModel(),
            'request'            => $this->getRequest()->getPost(),
        );

        Mage::dispatchEvent('adminhtml_sales_order_create_process_data', $eventData);

        $this->_getOrderCreateModel()
            ->saveQuote();

        if ($paymentData = $this->getRequest()->getPost('payment')) {
            $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
        }

        /**
         * Saving of giftmessages
         */
        $giftmessages = $this->getRequest()->getPost('giftmessage');
        if ($giftmessages) {
            $this->_getGiftmessageSaveModel()->setGiftmessages($giftmessages)
                ->saveAllInQuote();
        }

        /**
         * Importing gift message allow items from specific product grid
         */
        if ($data = $this->getRequest()->getPost('add_products')) {
            $this->_getGiftmessageSaveModel()
                ->importAllowQuoteItemsFromProducts(Mage::helper('core')->jsonDecode($data));
        }

        /**
         * Importing gift message allow items on update quote items
         */
        if ($this->getRequest()->getPost('update_items')) {
            $items = $this->getRequest()->getPost('item', array());
            $this->_getGiftmessageSaveModel()->importAllowQuoteItemsFromItems($items);
        }

        $data = $this->getRequest()->getPost('order');
        if (!empty($data['coupon']['code'])) {
            if ($this->_getQuote()->getCouponCode() !== $data['coupon']['code']) {
                $this->_getSession()->addError($this->__('"%s" coupon code is not valid.', $data['coupon']['code']));
            } else {
                $this->_getSession()->addSuccess($this->__('The coupon code has been accepted.'));
            }
        }

        return $this;
    }

}
