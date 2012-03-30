<?php
/**
 * @author      Branko Ajzele, ajzele@gmail.com
 * @category    Inchoo
 * @package     Inchoo_Api
 * @copyright   Copyright (c) Inchoo LLC (http://inchoo.net)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Inchoo_Api_Model_Api_Server_Adapter_Amf extends Varien_Object implements Mage_Api_Model_Server_Adapter_Interface
{
     /**
      * REST Server
      *
      * @var Zend_Amf_Server
      */
     protected $_amf = null;

     /**
     * Set handler class name for webservice
     *
     * @param string $handler
     * @return Inchoo_Api_Model_Api_Server_Adapter_Amf
     */
    public function setHandler($handler)
    {
        $this->setData('handler', $handler);
        return $this;
    }

    /**
     * Retrive handler class name for webservice
     *
     * @return string
     */
    public function getHandler()
    {
        return $this->getData('handler');
    }

     /**
     * Set webservice api controller
     *
     * @param Mage_Api_Controller_Action $controller
     * @return Inchoo_Api_Model_Api_Server_Adapter_Amf
     */
    public function setController(Mage_Api_Controller_Action $controller)
    {
         $this->setData('controller', $controller);
         return $this;
    }

    /**
     * Retrive webservice api controller
     *
     * @return Mage_Api_Controller_Action
     */
    public function getController()
    {
        return $this->getData('controller');
    }

    /**
     * Run webservice
     *
     * @param Mage_Api_Controller_Action $controller
     * @return Inchoo_Api_Model_Api_Server_Adapter_Amf
     */
    public function run()
    {
        $apiConfigCharset = Mage::getStoreConfig("api/config/charset");
        
        $this->_amf = new Zend_Amf_Server();
        
        $this->_amf->setClass($this->getHandler());
        
        $this->getController()->getResponse()
            ->clearHeaders()
            ->setHeader('Content-Type','application/x-amf; charset='.$apiConfigCharset)
            ->setBody($this->_amf->handle());
        
        return $this;
    }

    /**
     * Dispatch webservice fault
     *
     * @param int $code
     * @param string $message
     */
    public function fault($code, $message)
    {
        throw new Zend_Json_Server_Exception($message, $code);
    }
}