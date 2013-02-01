<?php
/**
 * @author      troyer <troyer@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */
class Mage_Adminhtml_Block_Catalog_Category_Tab_Renderer_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    public function _getValue(Varien_Object $row) {
        $status = 'Yes';
        if($row->getData('status')){
            $statusId = $row->getData('status');
            if($statusId == 1) {
                $status = 'Yes';
            } else {
                $status = 'No';
            }
        }
        return $status;
    }
}