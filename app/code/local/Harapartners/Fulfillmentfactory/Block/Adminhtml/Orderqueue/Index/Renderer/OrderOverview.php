<?php

/*
 * NOTICE OF LICENSE
 *
 */
class Harapartners_Fulfillmentfactory_Block_Adminhtml_Orderqueue_Index_Renderer_OrderOverview extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function _getValue(Varien_Object $row) {
       $filter = array(
            'order_increment_id' => $row->getIncrementId()
        );
       $encoded = base64_encode(http_build_query($filter));
       
       //$html = "<img style='cursor:pointer' onclick='newPopup(" .'"' . $this->getUrl("fulfillmentfactory/adminhtml_itemqueue/index", array('filter' => $encoded)) .'"'  . ")' src='/skin/adminhtml/default/harapartners/images/view_icon.png'  width='20' height='20' alt='quick view'/>";
       //$html = "<img style='cursor:pointer' onclick='newPopup(" . '"' . base64_encode($content) .'"'  . ")' src='/skin/adminhtml/default/harapartners/images/view_icon.png'  width='20' height='20' alt='quick view'/>";
       $html = "<img style='cursor:pointer' onclick='newPopup(" .'"' . $this->getUrl("fulfillmentfactory/adminhtml_orderqueue/orderquickview", array('filter' => $encoded)) .'"'  . ")' src='/skin/adminhtml/default/harapartners/images/view_icon.png'  width='20' height='20' alt='quick view'/>";
       return $html;
    }
}

?>