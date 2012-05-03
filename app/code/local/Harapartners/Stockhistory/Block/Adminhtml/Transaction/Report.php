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

class Harapartners_Stockhistory_Block_Adminhtml_Transaction_Report extends Mage_Adminhtml_Block_Widget_Grid_Container {
    
    public function __construct() {
        $dataObject = new Varien_Object(Mage::registry('stockhistory_transaction_report_data'));
        parent::__construct();
        $this->_controller = 'adminhtml_transaction_report';
        $this->_blockGroup = 'stockhistory';
        
        $headerText = 'Product Report from PO ' . $dataObject->getData('po_id');
        $poObject = Mage::getModel('stockhistory/purchaseorder')->load($dataObject->getData('po_id'));
        if($poObject->getStatus() == Harapartners_Stockhistory_Model_Purchaseorder::STATUS_SUBMITTED){
            $headerText .= ' (Submitted)';
        }
        
        $this->_headerText = Mage::helper('stockhistory')->__($headerText);
        $this->_removeButton('add');
        
        if($poObject->getStatus() == Harapartners_Stockhistory_Model_Purchaseorder::STATUS_OPEN){
            $this->_addButton('submit_dotcom_po', array(
                'label'     => Mage::helper('stockhistory')->__('Submit to DOTcom'),
                'onclick'   => 'submitDotcomPo()',
                'class'        => 'save'
            ));
            
            $this->_addButton('post_batch_amendments', array(
                'label'     => Mage::helper('stockhistory')->__('Post Batch Amendments'),
                'onclick'   => 'postBatchAmendment()',
                'class'        => 'add'
            ));
        }
        
        $this->_addButton('print_report', array(
            'label'     => Mage::helper('stockhistory')->__('Print Report'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('stockhistory/adminhtml_transaction/print/po_id/' . $dataObject->getData('po_id')) . '\')',
            'class'        => 'save'
        ));
    }
    
    //Addtional JS, added to the page in a clean way without touching the template
    protected function _toHtml(){
        $dataObject = new Varien_Object(Mage::registry('stockhistory_transaction_report_data'));
        $postFormKey = $this->getFormKey();
        $postFormUrl = $this->getUrl('stockhistory/adminhtml_transaction/postBatchAmendment/', array('po_id' => $dataObject->getData('po_id')));
        $html = parent::_toHtml();
        //Wrapping HTML as a form, we use the Grid Widget for the look only, not for any functionalities
        $html .= <<<FORM_WRAPPER
<div style="display: none;">
    <form method="post" action="$postFormUrl" id="post_batch_amendment_form">
        <input type="hidden" value="$postFormKey" name="form_key" />
    </form>
</div>
FORM_WRAPPER;
        
        $hasEmptyMasterPackItem = Mage::registry('has_empty_master_pack_item') ? 1 : 0;
        $html .= <<<ADDITIONAL_JAVASCRIPT
<script type="text/javascript">
    var postBatchAmendment = function () {
        if(confirm('All amendment quantities will be posted to this purchase order, continue?')) {
            var emptyPostForm = jQuery('#post_batch_amendment_form');
            var shouldUpdate = false;
            jQuery("input.qty_to_amend").each(function (){
                emptyPostForm.append(jQuery(this).clone());
                if(jQuery(this).is(":visible") && !!jQuery(this).val()){
                    shouldUpdate = true;
                }
            });
            if(shouldUpdate){
                emptyPostForm.submit();
            }else{
                alert('Nothing to amend.');
            }
        }
    }
    
    var hasEmptyMasterPackItem = $hasEmptyMasterPackItem;
    var submitDotcomPo = function (){
        if(hasEmptyMasterPackItem){
            if(confirm('Some rows have zero Total Qty, and will be ignored. continue?')) {
                setLocation('{$this->getUrl('stockhistory/adminhtml_transaction/submitToDotcom/po_id/' . $dataObject->getData('po_id'))}');
            }
        }else{
            setLocation('{$this->getUrl('stockhistory/adminhtml_transaction/submitToDotcom/po_id/' . $dataObject->getData('po_id'))}');
        }
    }
</script>
ADDITIONAL_JAVASCRIPT;

        return $html;
    }

}