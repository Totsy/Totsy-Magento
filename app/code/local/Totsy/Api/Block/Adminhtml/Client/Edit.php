<?php
/**
 * @category    Totsy
 * @package     Totsy\Api\Block\Adminhtml\Client
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Api_Block_Adminhtml_Client_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'totsy_api_client_id';
        $this->_blockGroup = 'totsyapi';
        $this->_controller = 'adminhtml_client';
    }

    public function getHeaderText() {
        return Mage::helper('totsyapi')->__('Add Totsy API Client');
    }

    public function getSaveUrl(){
        return $this->getUrl('*/*/save', array('_current'=>true));
    }
    
    
}