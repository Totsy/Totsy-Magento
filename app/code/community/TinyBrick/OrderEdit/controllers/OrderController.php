<?php
/**
 * TinyBrick Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the TinyBrick Commercial Extension License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.delorumcommerce.com/license/commercial-extension
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@tinybrick.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package to newer
 * versions in the future. 
 *
 * @category   TinyBrick
 * @package    TinyBrick_OrderEdit
 * @copyright  Copyright (c) 2010 TinyBrick Inc. LLC
 * @license    http://store.delorumcommerce.com/license/commercial-extension
 * @Modifications : Tom Royer troyer@totsy.com
 */
class TinyBrick_OrderEdit_OrderController extends Mage_Adminhtml_Controller_Action
{   
    #Only Payment/Billing Informations can be Edited
    public function editAction()
    {
        $order = $this->_initOrder();
        //arrays for restoring order if error is thrown or payment is declined
        $orderArr = $order->getData();
        $billingArr = $order->getBillingAddress()->getData();
        if($order->getShippingAddress()) {
            $shippingArr = $order->getShippingAddress()->getData();
        } else {
            $shippingArr = null;
        }
        try {
            $preTotal = $order->getGrandTotal();
            $edits = array();
            foreach($this->getRequest()->getParams() as $param) {
                if(substr($param,0,1) == '{') {
                if($param = Zend_Json::decode($param)) {
                    $edits[] = $param;
                    }
                }
            }
            $msgs = array();
            
            $changes = array();
            $addressUpdated = true;

            foreach($edits as $edit) {
                if($edit['type']) {
                    if($edit['type'] == 'billing') {
                        $model = Mage::getModel('orderedit/edit_updater_type_'.$edit['type']);
                        if($mess = $model->edit($order,$edit)) {
                            if($mess == 'not_updated') {
                                $addressUpdated = false;
                            } else {
                                $msgs[] = $mess;
                            }
                        } 
                    }
                }
            }

            foreach($edits as $edit) {
                if($edit['type']) {
                    if($edit['type'] == 'eitems' || $edit['type'] == 'nitems' || $edit['type'] == 'shipping') {
                        $model = Mage::getModel('orderedit/edit_updater_type_'.$edit['type']);
                        if(!$changes[] = $model->edit($order,$edit)) {
                            $msgs[] = "Error updating " . $edit['type'];
                        }
                    }
                }
            }
            #After Editing Billing Informations use it to Update Payment Infos
            foreach($edits as $edit) {
                if($edit['type']) {
                    if($edit['type'] == 'payment') {
                        $edit['addressUpdated'] = $addressUpdated;
                        $model = Mage::getModel('orderedit/edit_updater_type_'.$edit['type']);
                        if($mess = $model->edit($order,$edit)) {
                            $msgs[] = $mess;
                        }
                    }
                }
            }
            //if($this->getRequest()->getParam('coupon')) {
            //    $order->setCouponCode($this->getRequest()->getParam('coupon'));
            //}
            $order->collectTotals()->save();
            Mage::getSingleton('cataloginventory/stock_status')->syncStatusWithStock($order);
            $postTotal = $order->getGrandTotal();
            if(count($msgs) < 1) {
                //auth for more if the total has increased and configured to do so
                if(Mage::getStoreConfig('toe/orderedit/auth')) {
                    if($postTotal > $preTotal) {
                        $payment = $order->getPayment();
                        $orderMethod = $payment->getMethod();
                        if($orderMethod != 'free' && $orderMethod != 'checkmo' && $orderMethod != 'purchaseorder') {
                            if(!$payment->authorize(1, $postTotal)) {
                                //$this->_orderRollBack($order, $orderArr, $billingArr, $shippingArr);
                                echo "There was an error re-authorizing payment.";
                                return $this;
                            }
                        }
                    }
                }
                //fire event and log changes
                Mage::dispatchEvent('orderedit_edit', array('order'=>$order));
                $this->_logChanges($order, $this->getRequest()->getParam('comment'), $this->getRequest()->getParam('admin_user'), $changes);
                echo "Order updated successfully. The page will now refresh.";
            } else {
                $this->_orderRollBack($order, $orderArr, $billingArr, $shippingArr);
                echo "There was an error saving information, please try again. : " . $msgs[0];
            }
        } catch(Exception $e) {
            echo $e->getMessage();
            $this->_orderRollBack($order, $orderArr, $billingArr, $shippingArr);
        }
        return $this;
    }
    
    protected function _orderRollBack($order, $orderArray, $billingArray, $shippingArray)
    {
        $order->setData($orderArray)->save();
        $order->getBillingAddress()->setData($billingArray)->save();
        if($order->getShippingAddress()) {
            $order->getShippingAddress()->setData($shippingArray)->save();
        }
        $order->save();
    }
    
    protected function _logChanges($order, $comment, $user, $array = array()) 
    {
        $logComment = $user . " made changes to this order. <br /><br />";
        foreach($array as $change) {
            if($change != 1) {
                $logComment .= $change;
            }
        }
        $logComment .= "<br />User comment: " . $comment;
        $status = $order->getStatus();
        $notify = 0;
        $order->addStatusToHistory($status, $logComment, $notify);
        $order->save();
    }
    
    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('orderedit/order')->load($id);

        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);
        return $order;
    }
    
    public function updateCommentAction()
    {
        if ($order = $this->_initOrder()) {
            echo $this->getLayout()->createBlock('adminhtml/sales_order_view_history')->setTemplate('sales/order/view/history.phtml')->toHtml();
        }
    }
    
    public function recalcAction()
    {
        echo $this->getLayout()->createBlock('orderedit/adminhtml_sales_order_shipping_update')->setTemplate('sales/order/view/tab/shipping-form.phtml')->toHtml();
    }
    
    public function newItemAction()
    {
        echo $this->getLayout()->createBlock('orderedit/adminhtml_sales_order_view_items_add')->setTemplate('sales/order/view/items/add.phtml')->toHtml();
    }
    
    public function getQtyAndDescAction()
    {
        $sku = $this->getRequest()->getParam('sku');

        $product = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('sku', $sku)
            ->getFirstItem();
        $return = array();
        $return['name'] = $product->getName();
        
        if($product->getSpecialPrice()) {
            $return['price'] = round($product->getSpecialPrice(), 2);
        } else {
            $return['price'] = round($product->getPrice(), 2);
        }

        $qty = (int) Mage::getModel('catalog/product')->load($product->getId())->getStockItem()->getQty();
        if($qty>9) {
            $qty = 9;
        }

        $select = "<select class='n-item-qty'>";
        $x = 1;
        while($x <= $qty) {
            $select .= "<option value='" . $x . "'>" . $x . "</option>";
            $x++;
        }
        $select .= "</select>";
        $return['select'] = $select;
        echo Zend_Json::encode($return);
    }

    public function getOrderAddressInformationsAction()
    {
        $addressId = $this->getRequest()->getParam('addressId');
        $address = Mage::getModel('customer/address')->load($addressId);
        echo Zend_Json::encode($address->getData());
    }

    public function checkCouponAvailabilityAction()
    {
        $couponCode = $this->getRequest()->getParam('coupon');
        $coupon = Mage::getModel('salesrule/coupon');
        $coupon->load($couponCode, 'code');
        $result = array('available' => false);
        if($coupon->getId()) {
            if(strtotime($coupon->getExpirationDate()) > time()) {
                $result = array('available' => true);
            }
        }
        echo Zend_Json::encode($result);
    }
}