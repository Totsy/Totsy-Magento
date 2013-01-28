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
           if ($this->_isAllowedAction('submit_dotcom')) {
                $this->_addButton('submit_dotcom_po', array(
                    'label'     => Mage::helper('stockhistory')->__('Submit to DOTcom'),
                    'onclick'   => 'submitDotcomPo()',
                    'class'        => 'save'
                ));
            }
            
            if ($this->_isAllowedAction('post_amendment')) {
                $this->_addButton('post_batch_amendments', array(
                    'label'     => Mage::helper('stockhistory')->__('Post Batch Amendments'),
                    'onclick'   => 'postBatchAmendment()',
                    'class'        => 'add'
                ));
            }
        }
        
        $this->_addButton('print_report', array(
            'label'     => Mage::helper('stockhistory')->__('Print Report'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('stockhistory/adminhtml_transaction/print/po_id/' . $dataObject->getData('po_id')) . '\')',
            'class'        => 'save'
        ));
    }
    protected function _isAllowedAction($action)
    {
        //return null;
        return Mage::getSingleton('admin/session')->isAllowed('harapartners/stockhistory/purchaseorder/actions/' . $action);
    }
    
    //Addtional JS, added to the page in a clean way without touching the template
    protected function _toHtml(){
        $dataObject = new Varien_Object(Mage::registry('stockhistory_transaction_report_data'));
        $postFormKey = $this->getFormKey();
        $postFormUrl = $this->getUrl('stockhistory/adminhtml_transaction/postBatchAmendment/', array('po_id' => $dataObject->getData('po_id')));
        $html = parent::_toHtml();
        $localUrl = $this->getUrl();
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
            goodData = getNonEmptyFields();
            goodData = JSON.stringify(goodData);
            request_url = emptyPostForm[0].action;
            new Ajax.Request(request_url, {
                parameters:{qty_to_amend: goodData, form_key: emptyPostForm[0].form_key.value},
                onSuccess: function() {
                    alert("About to refresh the page...");
                    setLocation("{$this->getUrl('*/adminhtml_transaction/report', array("_current" => true))}");
                }
            });
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
    var getNonEmptyFields = function(){
        initial = $$('input[name*=[qty_to_amend]]');
        goodData = {};

        initial.each(function(index){
            if(index.value){
                name = index.name;
               /* breakup = name.split('[');
                sku = breakup[1];
                sku = sku.substring(0,sku.length-1);*/
                sku = index.id;
                if (!goodData[sku]) {
                    data = $$('input[name*=[' + sku + ']]');
                    goodData[sku] = {};
                    goodData[sku]['qty_to_amend'] = index.value;
                    goodData[sku]['qty_total'] = data[1].value;
                    goodData[sku]['unit_cost'] = data[2].value;
                }
                
            }
        });
        return goodData;
    }

ADDITIONAL_JAVASCRIPT;
    if($this->_isAllowedAction('editable')){
    $html.=<<<ADDITIONAL_JAVASCRIPT
jQuery(document).ready(function() {
        var oTable = jQuery('#ReportGrid_table').dataTable( {
            "bPaginate": false,
            "bSort": false,
            "bFilter": false,
            "bProcessing": false
        } );

        jQuery('.editable').editable( "{$this->getUrl('stockhistory/adminhtml_transaction/updateCasePackGroup', array('_current' => true, '_query' => array('isAjax' => "true")))}", {
            "placeholder" : "Click to edit",
            "indicator" : '<img src="/skin/adminhtml/default/enterprise/images/process_spinner.gif"/>',
            "method": "POST",
            "name": "change_to",
            "callback": function( sValue, y ) {
               /* Redraw the table from the new data on the server */
               response = JSON.parse(sValue);
               var aPos = oTable.fnGetPosition( this );
               oTable.fnUpdate( response.response, aPos[0], aPos[1] );
               
               if(response.update) {
                   jQuery.each(response.update, function(index, data){
                        jQuery('#' + data.sku).val(data.qty_to_amend);
                    });
                }
                
                showMessages(response.message);
            },
            "submitdata": function ( value, settings ) {
                //get product id
                element = oTable.fnGetData(oTable.fnGetPosition( this )[0],0);
                obj = jQuery(element);
                
                //get cell "id"
                var classes = jQuery(this).attr('class');
                classes_ar = classes.split(" ", 3);
                var cell_id = classes_ar[2];
                return {
                    "id" : cell_id,
                    "product_id": obj.val(),
                    "form_key" : FORM_KEY,
                    "column": oTable.fnGetPosition( this )[2]
                };
            },
            "submit": 'OK',
            "cancel": 'Cancel',
            "height": "14px",
            "width": "100%"
        } );

        jQuery(".editable").click(function(){
            value = jQuery("input[name='change_to']").val();
            if(jQuery.trim(value) == "Click to edit") {
                jQuery("input[name='change_to']").val("");
            }
        });
        
        function showMessages(messages){
            jQuery('#messages').html("");
            html = "<ul class='messages'>";
            jQuery.each(messages, function(index, data){
                html += "<li class=" + data['type'] + "-msg>";
                html += "<ul><li>" + data['message'] + "</li></ul>";
            });
            jQuery('#messages').append(html);
            /*jQuery('#messages').fadeOut(10000, function(){
                jQuery(this).css('display','block');
            });*/
        }        
});
        var clearFields = function() {
            jQuery('.input-text').each(function(index,elem){
              elem['value'] ="";
            });
        };
        
        var displayCasePackMath = function(){
                jQuery.ajax({
                    url: "{$this->getUrl('*/*/displayCasePackMath', array('_current' => true, '_query' => array('isAjax' => "true")))}",
                    dataType: 'json',
                    success: function(response) {
                        
                        if(response.update){
                            jQuery.each(response.update, function(index, data){
                                jQuery('#' + index).val(data);
                            });
                        }
                        if(response.message){
                            alert(":( received an error");
                        }
                    }
                        
                });
        }
</script>
ADDITIONAL_JAVASCRIPT;
        }
        return $html;
    }

}
