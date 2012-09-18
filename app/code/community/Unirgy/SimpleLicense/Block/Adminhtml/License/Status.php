<?php

class Unirgy_SimpleLicense_Block_Adminhtml_License_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $status = $row->getLicenseStatus();

        switch ($status) {
            case 'inactive': $class = 'critical'; break;
            case 'invalid': $class = 'major'; break;
            case 'expired': $class = 'minor'; break;
            case 'active': $class = 'notice'; break;
            default: $class = 'minor';
        }
        return '<span class="grid-severity-'.$class.'"><span>'.$status.'</span></span>';
    }
}