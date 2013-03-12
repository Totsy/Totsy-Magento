<?php
/**
 * @category    Totsy
 * @package     Harapartners_Customertracking_Helper
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */
 
class Harapartners_Customertracking_Helper_CustomPixel
{
    const TRIALPAY_SHARED_KEY = '98b57df81d5e93501539700286314bedd73802e25df127db026c196eefef3d8d';

    /**
     * Generate a TrialPay pixel query string.
     * This includes parameters tp_t (Unix timestamp), tp_sid (clickId from user
     * registration), and tp_v1 (HMAC-MD5 signed hash of other parameters).
     *
     * @return string
     */
    public function trialpay()
    {
        $trackingInfo = Mage::getSingleton('customer/session')
            ->getData('affiliate_info');
        $regParams = json_decode($trackingInfo['registration_param'], true);
        $regParams = array_change_key_case($regParams);

        $queryParams = array(
            'tp_t' => time(),
            'tp_sid' => $regParams['subid'],
        );

        $queryParams['tp_v1'] = hash_hmac(
            'md5',
            http_build_query($queryParams),
            self::TRIALPAY_SHARED_KEY
        );

        return http_build_query($queryParams);
    }

    /**
     * Calculate the total commission value for the last order placed, as 50% of
     * the total profit.
     * NOTE: the commission percentage should ideally be a method argument, but
     * the way this method is invoked (via tracking code template variables)
     * makes it awkward.
     *
     * @return string
     */
    public function commision50()
    {
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        $order = Mage::getModel('sales/order')->load($orderId);

        return number_format($order->getProfit() / 2, 2);
    }

    public function linkshare($pixel) {

        $html = '';
        $login_time = Mage::getSingleton('customer/session')->getData('CUSTOMER_LAST_VALIDATION_TIME');
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        $trackingInfo = Mage::getSingleton('customer/session')->getData('affiliate_info');
        $customertracking = Mage::getModel('customertracking/record')->loadByCustomerEmail($customer->getEmail());
        
        //Making sure that the user is actually a linkshare user
        if(trim($customertracking->getData('affiliate_code')) != 'linkshare'  && $customertracking->getId() != 0) {
            Mage::log("This is not a linkshare customer.", Zend_Log::INFO, 'linkshare_log.log');
            return $html;
        }

        $orderids = Mage::getSingleton('checkout/session')->getData('orderids');
        $regParams = json_decode($customertracking->getData('registration_param'),true);

        $html = '';
        if(count($orderids) >= 2  && is_null($orderId)) {
            Mage::log("Multiple Orders:", Zend_Log::INFO, 'linkshare_log.log');
            $split_count = 1;

            $split_orders = Mage::getModel('sales/order')->getCollection();
            $split_orders->getSelect()->where('increment_id in (' . implode(',', $orderids) . ')');
            Mage::log("\tOrder(s) count: " . $split_orders->count(), Zend_Log::INFO, 'linkshare_log.log');
            foreach($split_orders as $order) {
                $message = Mage::helper('linkshare/linkshare')->linkshareRaw($order, $regParams['subID'],$login_time,'Pixel');
                $encode = Mage::helper('linkshare/linkshare')->prepareTransactionData($message);
                $result = Mage::helper('linkshare/linkshare')->sendTransaction($encode, $order->getIncrementId(), $order->getStatus());
                $result['customertracking_id'] = (int)$customertracking->getCustomertrackingId();
                $result['raw_data'] = $message;
                $result['order_status'] = 'New';
                Mage::getModel('linkshare/transactions')->recordTransaction($result);
                if ($split_count == 1) {
                    $html .= $message . "\"/>\n";
                } else {
                    $html .= preg_replace('/{{[\w.]+}}/', $message, $pixel);
                }
                ++$split_count;
                Mage::log("\tThis is order id: {$order->getId()} and it is has a status: {$order->getStatus()}", Zend_Log::INFO, 'linkshare_log.log');
            }
            $html = rtrim($html, "\"/>");
        } else {
            Mage::log("Single Order: ", Zend_Log::INFO, 'linkshare_log.log');
            $order = Mage::getModel('sales/order')->load($orderId);
            $message = Mage::helper('linkshare/linkshare')->linkshareRaw($order, $regParams['subID'],$login_time,$order->getStatus());
            $html .= $message;
            $encode = Mage::helper('linkshare/linkshare')->prepareTransactionData($message);
            $result = Mage::helper('linkshare/linkshare')->sendTransaction($encode, $order->getIncrementId(), $order->getStatus());
            $result['customertracking_id'] = (int)$customertracking->getCustomertrackingId();
            $result['raw_data'] = $message;
            $result['order_status'] = 'New';
            Mage::getModel('linkshare/transactions')->recordTransaction($result);
            Mage::log("\tThis is order id: {$orderId} and it is has a status: {$order->getStatus()}", Zend_Log::INFO, 'linkshare_log.log');
        
        }

        return $html;
    }
}
