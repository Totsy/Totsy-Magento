<?php
/**
 * @category    Totsy
 * @package     Totsy_Core_Model_Logger
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

/**
 * Implements the Totsy_Core module logger to provide basic logging
 * using Magento's built-in logging capabilities. Nothing more, nothing less.
 */
class Totsy_Core_Model_Logger_MageLogger
    implements Totsy_Core_Model_LoggerInterface
{
    /**
     * Log an entry at the DEBUG level.
     *
     * @abstract
     * @param string $message The log message.
     * @param mixed  $context Any message context.
     *
     * @return bool
     */
    public function debug($message, $context = null)
    {
        Mage::log($message, Zend_Log::DEBUG);
        return true;
    }

    /**
     * Log an entry at the INFO level.
     *
     * @abstract
     * @param string $message The log message.
     * @param mixed  $context Any message context.
     *
     * @return bool
     */
    public function info($message, $context = null)
    {
        Mage::log($message, Zend_Log::INFO);
        return true;
    }

    /**
     * Log an entry at the INFO level.
     *
     * @abstract
     * @param string $message The log message.
     * @param mixed  $context Any message context.
     *
     * @return bool
     */
    public function notice($message, $context = null)
    {
        Mage::log($message, Zend_Log::NOTICE);
        return true;
    }

    /**
     * Log an entry at the WARN level.
     *
     * @abstract
     * @param string $message The log message.
     * @param mixed  $context Any message context.
     *
     * @return bool
     */
    public function warn($message, $context = null)
    {
        Mage::log($message, Zend_Log::WARN);
        return true;
    }

    /**
     * Log an entry at the ERROR level.
     *
     * @abstract
     * @param string $message The log message.
     * @param mixed  $context Any message context.
     *
     * @return bool
     */
    public function err($message, $context = null)
    {
        Mage::log($message, Zend_Log::ERR);
        return true;
    }

    /**
     * Log an entry at the CRITICAL level.
     *
     * @abstract
     * @param string $message The log message.
     * @param mixed  $context Any message context.
     *
     * @return bool
     */
    public function crit($message, $context = null)
    {
        Mage::log($message, Zend_Log::CRIT);
        return true;
    }
}
