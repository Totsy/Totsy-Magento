<?php

class Unirgy_SimpleUp_Block_Adminhtml_Module_Nl2br extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return nl2br($this->_getValue($row));
    }
}