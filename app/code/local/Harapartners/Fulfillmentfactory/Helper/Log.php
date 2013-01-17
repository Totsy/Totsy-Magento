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
class Harapartners_Fulfillmentfactory_Helper_Log
    extends Mage_Core_Helper_Abstract
{
    /**
     * Log an informational message.
     *
     * @param string $message The message to log.
     * @return string The log file name.
     */
    public function infoLog($message)
    {
        Mage::log($message, Zend_Log::INFO, 'fulfillment_info.log', true);
    }

    /**
     * Log an error message.
     *
     * @param string $message The message to log.
     * @return string The log file name.
     */
    public function errorLog($message)
    {
        $logFileName = 'fulfillment_error.log';

        Mage::log($message, Zend_Log::ERR, $logFileName, true);

        return $logFileName;
    }

    /**
     * log message with order id
     *
     * @param string $message
     * @param int    $orderId
     * @param string $xml
     *
     * @return string file name
     */
    public function errorLogWithOrder($message, $orderId, $xml = '')
    {
        $logFileName = 'fulfillment_error_' . date('Y_m_d_his') . '.log';

        $errorlogModel = Mage::getModel('fulfillmentfactory/errorlog');
        $errorlogModel->setOrderId($orderId);
        $errorlogModel->setMessage($message);
        $errorlogModel->setXml($xml);
        $errorlogModel->save();

        Mage::log($message, Zend_Log::ERR, $logFileName);
        Mage::log($xml, Zend_Log::DEBUG, $logFileName);

        return $logFileName;
    }

    /**
     * Remove all existing fulfillment error log entries associated with an order.
     *
     * @param $order
     * @return int
     */
    public function removeErrorLogEntriesForOrder($order)
    {
        $logs = Mage::getModel('fulfillmentfactory/errorlog')->getCollection();
        $logs->addFieldToFilter('order_id', $order->getId());

        foreach ($logs as $errorlog) {
            $errorlog->isDeleted(true);
            $errorlog->delete();
        }
    }
}
