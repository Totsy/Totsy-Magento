<?php

class Unirgy_RapidFlow_Block_Adminhtml_Profile_Grid_Status
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $hlp = Mage::helper('urapidflow');

        $key = $this->getColumn()->getIndex();
        $value = $row->getData($key);

        switch ($key) {
        case 'profile_status':
            $classes = array('disabled'=>'critical', 'enabled'=>'notice');
            $labels = Mage::getSingleton('urapidflow/source')->setPath('profile_status')->toOptionHash();
            break;

        case 'run_status':
            $classes = array('idle'=>'notice', 'pending'=>'minor', 'running'=>'major', 'paused'=>'minor', 'stopped'=>'critical', 'finished'=>'notice');
            $labels = Mage::getSingleton('urapidflow/source')->setPath('run_status')->toOptionHash();
            break;

        case 'invoke_status':
            $classes = array('none'=>'minor', 'foreground'=>'critical', 'ondemand'=>'notice', 'scheduled'=>'major');
            $labels1 = array('foreground'=>$this->__('ForeGrnd'), 'ondemand'=>$this->__('OnDemand'), 'scheduled'=>$this->__('Schedule'));
            $labels = Mage::getSingleton('urapidflow/source')->setPath('invoke_status')->toOptionHash();
            break;

        default:
            return $value;
        }

        return '<span class="grid-severity-'.$classes[$value].'" '.(!empty($styles[$value])?' style="'.$styles[$value].'"':'').'><span>'
            .(!empty($labels1[$value]) ? $labels1[$value] : $labels[$value])
            .'</span></span>';
    }
}