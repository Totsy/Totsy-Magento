<?php 

class Totsy_Adminhtml_Block_Sales_Creditmemo extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_controller = 'sales_creditmemo';
        $this->_headerText = Mage::helper('sales')->__('Credit Memos');
        parent::__construct();
        $this->_removeButton('add');

        $this->_addButton('import', array(
            'label'     => Mage::helper('sales')->__('Import'),
            'onclick'   => "setLocation('".$this->getUrl('*/*/import')."')",
            'class'   => 'import'
        ));
    }
}

?>