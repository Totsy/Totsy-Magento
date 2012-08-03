<?php
/*
 * This is used to by pass the default renderer for datetime, and doesn't
 * perform any manipulation with the time.
 * 
*/
class Harapartners_Stockhistory_Block_Adminhtml_Widget_Grid_Column_Renderer_Date extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
          return $row->getData($this->getColumn()->getIndex());
         
    }
}

?>
