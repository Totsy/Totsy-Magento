<?php
/**
 * @category    Totsy
 * @package     Totsy_Core_Helper
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

use Monolog\Logger,
    Monolog\Handler\ChromePHPHandler,
    Monolog\Handler\StreamHandler,
    Monolog\Handler\GelfHandler,
    Gelf\MessagePublisher;

class Totsy_Core_Helper_Logger
{
    /**
     * Logger registry that stores all generated Logger objects, indexed by
     * logger name/identifier.
     *
     * @var array
     */
    protected $_loggers = array();

    /**
     * Get a logger identified by a unique name.
     *
     * @param string $name The unique name of the logger.
     *
     * @return Totsy_Core_Model_LoggerInterface
     */
    public function getLogger($name = 'default')
    {
        if (isset($this->_loggers[$name])) {
            return $this->_loggers[$name];
        }

        $appRoot = Mage::getRoot();
        $monologPath = realpath($appRoot . '/../lib/vendor/monolog/src');

        // check for the availability of the Monolog library
        if (is_dir($monologPath)) {
            require_once 'SplClassLoader.php';

            // setup a class autoloader for Monolog
            $monologLoader = new SplClassLoader('Monolog', $monologPath);
            $monologLoader->register();

            // setup a class autoloader for Gelf-PHP (logging to Graylog2)
            $gelfPath = realpath($appRoot . '/../lib/vendor/gelf-php/src');
            $gelfLoader = new SplClassLoader('Gelf', $gelfPath);
            $gelfLoader->register();

            // use configuration to build a new MonologLogger
            $config = Mage::getConfig()->getNode('loggers');
            if (false === $config || !$config->$name) {
                return new Totsy_Core_Model_Logger_MageLogger;
            }

            foreach ($config->$name as $loggerName => $loggerConfig) {
                $logger = new Logger($loggerName);

                foreach ($loggerConfig as $configKey => $configOptions) {
                    switch($configKey) {
                        case 'handlers':
                            foreach ($configOptions as $handlerName => $handlerOptions) {
                                $handler = $this->_getMonologHandler(
                                    $handlerName,
                                    $handlerOptions
                                );
                                $logger->pushHandler($handler);
                            }

                            break;
                    }
                }

                $this->_loggers[$loggerName] = $logger;

                return new Totsy_Core_Model_Logger_MonologLogger($logger);
            }
        } else {
            return new Totsy_Core_Model_Logger_MageLogger;
        }
    }

    /**
     * Create a new Monolog handler using a proprietary handler identifier, and
     * configuration options.
     *
     * @param $name string A generic name for the handler, from configuration.
     * @param $options array Configuration options for the handler.
     *
     * @return Monolog\Handler\HandlerInterface
     */
    protected function _getMonologHandler($name, $options)
    {
        $env = Mage::helper('core')->getEnvironment();
        $logLevel = Logger::DEBUG;

        if (isset($options['level'][$env])) {
            switch ($options['level'][$env]) {
                case 'DEBUG':
                    $logLevel = Logger::DEBUG;
                    break;
                case 'INFO':
                    $logLevel = Logger::INFO;
                    break;
                case 'WARN':
                    $logLevel = Logger::WARN;
                    break;
                case 'ERR':
                    $logLevel = Logger::ERR;
                    break;
                case 'CRIT':
                    $logLevel = Logger::CRIT;
                    break;
            }
        }

        switch ($name) {
            case 'stream':
                return new StreamHandler(
                    $options->getAttribute('filename'),
                    $logLevel
                );
            case 'chrome':
                return new ChromePHPHandler($logLevel);
            case 'gelf':
                echo "Created a new GELF logger at ", $options->getAttribute('hostname'), PHP_EOL;
                $publisher = new MessagePublisher(
                    $options->getAttribute('hostname')
                );
                return new \Monolog\Handler\GelfHandler($publisher, $logLevel);
        }
    }
}
