<?php
/*
 * NOTICE OF LICENSE
 *
 */
class Harapartners_Fulfillmentfactory_Block_Adminhtml_Itemqueue_Index_Renderer_POName extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    public function _getValue(Varien_Object $row) {
        $po_name = 'N/A';
        if($row->getData('po_name')){
               $po_name = $row->getData('po_name');
               $filter = array(
                    'id' => $row->getPoId(),
                    'name' => $po_name
                );
               $encoded = base64_encode(http_build_query($filter));
               $po_name = "<a target='_blank' href=" . $this->getUrl('stockhistory/adminhtml_purchaseorder', array('filter' => $encoded)) . "> {$po_name}</a>";
        }
        return $po_name;
    }
}
