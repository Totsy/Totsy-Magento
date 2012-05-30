<?php
/**
 * @category    Totsy
 * @package     Totsy_Core_Helper
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

use Monolog\Logger,
    Monolog\Handler\ChromePHPHandler,
    Monolog\Handler\StreamHandler;

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

            $monologLoader = new SplClassLoader('Monolog', $monologPath);
            $monologLoader->register();

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

    protected function _getMonologHandler($name, $options)
    {
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
                // echo "Setup a new Stream handler at level ", $logLevel, " to ", $options->getAttribute('filename'), PHP_EOL;
                return new StreamHandler(
                    $options->getAttribute('filename'),
                    $logLevel
                );
            case 'chrome':
                // echo "Setup a new Chrome handler at level ", $logLevel, PHP_EOL;
                return new ChromePHPHandler($logLevel);
        }
    }
}
