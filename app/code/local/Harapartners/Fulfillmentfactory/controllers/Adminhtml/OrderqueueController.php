<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */
class Harapartners_Fulfillmentfactory_Adminhtml_OrderqueueController extends Mage_Adminhtml_Controller_Action {
    
    /**
     * index page of order queue
     */
    public function indexAction() {        
        $this->_redirect('*/*/orderpending');
    }
    
    /**
     * index page of order queue(pending)
     */
    public function orderpendingAction() {
        $this->getRequest()->setParam('custom_status', 'pending');
        $this->loadLayout()
            ->_setActiveMenu('fulfillmentfactory/orderqueue')
            ->_addContent($this->getLayout()->createBlock('fulfillmentfactory/adminhtml_orderqueue_Index'))
            ->renderLayout();
    }
    
    /**
     * index page of order queue(fulfillment aging)
     */
    public function orderfulfillmentagingAction() {
        $this->getRequest()->setParam('custom_status', 'fulfillment_aging');
        $this->loadLayout()
            ->_setActiveMenu('fulfillmentfactory/orderqueue')
            ->_addContent($this->getLayout()->createBlock('fulfillmentfactory/adminhtml_orderqueue_Index'))
            ->renderLayout();
    }
    
    /**
     * index page of order queue(send to shipment)
     */
    public function orderfulfillmentAction() {
        $this->getRequest()->setParam('custom_status', 'processing');
        $this->loadLayout()
            ->_setActiveMenu('fulfillmentfactory/orderqueue')
            ->_addContent($this->getLayout()->createBlock('fulfillmentfactory/adminhtml_orderqueue_Index'))
            ->renderLayout();
    }
    
    /**
     * index page of order queue(shipment aging)
     */
    public function ordershipmentagingAction() {
        $this->getRequest()->setParam('custom_status', 'shipment_aging');
        $this->loadLayout()
            ->_setActiveMenu('fulfillmentfactory/orderqueue')
            ->_addContent($this->getLayout()->createBlock('fulfillmentfactory/adminhtml_orderqueue_Index'))
            ->renderLayout();
    }

    public function orderquickviewAction() {
        $encoded = $this->getRequest()->getParam('filter');
        $this->loadLayout('popup')
            ->_addContent($this->getLayout()->createBlock(
                'fulfillmentfactory/adminhtml_itemqueue_index'
            )->setData('filter', $encoded))
            ->renderLayout();
    }
}