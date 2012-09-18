<?php

class Unirgy_SimpleUp_Block_Adminhtml_Module_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $usimpleup = Mage::getConfig()->getNode("modules/{$row->getModuleName()}/usimpleup");
        return isset($usimpleup['changelog']) ? '<a href="'.$usimpleup['changelog'].'">'.$this->__('Changelog').'</a>' : '';
    }
}