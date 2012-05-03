<?php
class Harapartners_Fulfillmentfactory_Block_Adminhtml_Orderqueue_Index extends Mage_Adminhtml_Block_Widget_Grid_Container{
    
    public function __construct(){
        parent::__construct();
        
        $this->_blockGroup = 'fulfillmentfactory';
        $this->_controller = 'adminhtml_orderqueue_index';
        
        $status = $this->getRequest()->getParam('custom_status');
        
        $this->_headerText = $this->_getHeaderText($status);
           $this->_removeButton('add');
    }
    
    /**
     * get header text by different scenario
     *
     * @param string $status
     * @return string
     */
    protected function _getHeaderText($status) {
        $headerText = 'Order Queue';
        
        switch($status) {
            case 'pending':
                $headerText = 'Order Queue (Pending)';
                break;
            case 'fulfillment_aging':
                $headerText = 'Order Queue (Fulfillment Aging)';
                break;
            case 'processing':
                $headerText = 'Order Queue (Sent to Fulfillment)';
                break;
            case 'shipment_aging':
                $headerText = 'Order Queue (Shipment Aging)';
                break;
        }
        
        return $headerText;
    }
 
}