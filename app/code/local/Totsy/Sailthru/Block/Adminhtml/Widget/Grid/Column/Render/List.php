<?php

class Totsy_Sailthru_Block_Adminhtml_Widget_Grid_Column_Render_List
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action {

    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }

	public function _getValue(Varien_Object $row)
    {
        if ($getter = $this->getColumn()->getGetter()) {
            $val = $row->$getter();
        }
        $val = $row->getData($this->getColumn()->getIndex());

        if (empty($val)){
            return '';
        }
        $val = json_decode($val,true);
        $val = implode(",", $val);

        $out = '<textarea rows="2" cols="5">';
        $out .= $val;
        $out .= '</textarea>';

        return $out;
    }

}