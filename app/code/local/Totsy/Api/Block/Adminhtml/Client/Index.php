<?php
/**
 * @category    Totsy
 * @package     Totsy\Api\Block\Adminhtml\Client
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Api_Block_Adminhtml_Client_Index
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_controller = 'adminhtml_client_index';
        $this->_blockGroup = 'totsyapi';
        $this->_headerText = Mage::helper('totsyapi')->__('Totsy API Clients');
    }
}
