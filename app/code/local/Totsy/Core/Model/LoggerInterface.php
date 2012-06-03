<?php
/**
 * @category    Totsy
 * @package     Totsy_Core_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

interface Totsy_Core_Model_LoggerInterface
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
    public function debug($message, $context = null);

    /**
     * Log an entry at the INFO level.
     *
     * @abstract
     * @param string $message The log message.
     * @param mixed  $context Any message context.
     *
     * @return bool
     */
    public function info($message, $context = null);

    /**
     * Log an entry at the INFO level.
     *
     * @abstract
     * @param string $message The log message.
     * @param mixed  $context Any message context.
     *
     * @return bool
     */
    public function notice($message, $context = null);

    /**
     * Log an entry at the WARN level.
     *
     * @abstract
     * @param string $message The log message.
     * @param mixed  $context Any message context.
     *
     * @return bool
     */
    public function warn($message, $context = null);

    /**
     * Log an entry at the ERROR level.
     *
     * @abstract
     * @param string $message The log message.
     * @param mixed  $context Any message context.
     *
     * @return bool
     */
    public function err($message, $context = null);

    /**
     * Log an entry at the CRITICAL level.
     *
     * @abstract
     * @param string $message The log message.
     * @param mixed  $context Any message context.
     *
     * @return bool
     */
    public function crit($message, $context = null);
}
