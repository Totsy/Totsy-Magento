<?php

class Unirgy_SimpleUp_Block_Adminhtml_Module_Version extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $curVer = (string)Mage::getConfig()->getNode("modules/{$row->getModuleName()}/version");
        $lastVer = $row->getRemoteVersion();

        $compare = version_compare($curVer, $lastVer);
        if (!$curVer) {
            $status = '';
        } elseif (!$lastVer) {
            $status = 'major';
            #return '<span class="grid-severity-minor">'.$curVer.'</span>';
        } elseif ($compare==0) {
            $status = 'notice';
        } elseif ($compare==-1) {
            $status = 'critical';
            #return '<span class="grid-severity-major">'.$curVer.'</span>';
        } elseif ($compare==1) {
            $status = 'minor';
            #return '<span class="grid-severity-minor">'.$curVer.'</span>';
        }
        return '<span class="grid-severity-'.$status.'"><span>'.$curVer.'</span></span>';
    }
}