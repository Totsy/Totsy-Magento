<?php
/**
 * @category    Totsy
 * @package     Totsy_Core_Model_Logger
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

use Monolog\Logger;

/**
 * Implements the Totsy_Core module logger to provide advanced logging
 * functionality using the third-party Monolog library.
 *
 * @see Monolog\Logger
 */
class Totsy_Core_Model_Logger_MonologLogger
    implements Totsy_Core_Model_LoggerInterface
{
    /**
     * The Monolog logger object.
     *
     * @var Monolog\Logger
     */
    protected $_logger;

    public function __construct(Logger $logger) {
        $this->_logger = $logger;
    }

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
        return $this->_logger->debug($message, (array) $context);
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
        return $this->_logger->info($message, (array) $context);
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
        return $this->_logger->notice($message, (array) $context);
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
        return $this->_logger->warn($message, (array) $context);
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
        return $this->_logger->err($message, (array) $context);
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
        return $this->_logger->crit($message, (array) $context);
    }
}
