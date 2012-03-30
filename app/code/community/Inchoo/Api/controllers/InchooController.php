<?php
/**
 * @author      Branko Ajzele, ajzele@gmail.com
 * @category    Inchoo
 * @package     Inchoo_Api
 * @copyright   Copyright (c) Inchoo LLC (http://inchoo.net)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Inchoo_Api_InchooController extends Mage_Api_Controller_Action
{  
    /**
     * Access like http://magento.ce/index.php/api/inchoo/rest
     */    
    public function restAction()
    {
        /* inchoo_api_rest => HANDLER from api.xml */
        $this->_getServer()->init($this, 'inchoo_api_rest')
            ->run();
    }    
    
    /**
     * Access like http://magento.ce/index.php/api/inchoo/json
     */
    public function jsonAction()
    {
        /* inchoo_api_json => HANDLER from api.xml */
        $this->_getServer()->init($this, 'inchoo_api_json')
            ->run();
    }
    
    /**
     * Access like http://magento.ce/index.php/api/inchoo/amf
     */
    public function amfAction()
    {
        /* inchoo_api_amf => HANDLER from api.xml */
        $this->_getServer()->init($this, 'inchoo_api_amf')
            ->run();
    }    
}
