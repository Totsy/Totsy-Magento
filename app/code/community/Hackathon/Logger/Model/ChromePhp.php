<?php
/**
 * User: Tharsan
 * Date: 19.03.13
 * Time: 20:17
 */

require_once 'ChromePhp/ChromePhp.php';

class Hackathon_Logger_Model_ChromePhp extends Zend_Log_Writer_Abstract
{
    /**
     * Write a message to the log.
     *
     * @param  array $event  log data event
     *
     * @return void
     */
    protected function _write($event)
    {
        switch ($event['priority']) {
            case Zend_Log::INFO:
                ChromePhp::info($event['message']);
            case Zend_Log::NOTICE:
            case Zend_Log::WARN:
                ChromePhp::warn($event['message']);
            case Zend_Log::ALERT:
            case Zend_Log::ERR:
            case Zend_Log::CRIT:
                ChromePhp::error($event['message']);
                break;
            default:
                ChromePhp::log($event['message']);
        }
    }

    /**
     * Construct a Zend_Log driver
     *
     * @param  array|Zend_Config $config
     * @return Zend_Log_FactoryInterface
     */
    static public function factory($config)
    {
    }
}
