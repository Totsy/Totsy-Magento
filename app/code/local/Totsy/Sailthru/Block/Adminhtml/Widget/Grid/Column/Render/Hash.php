<?php

class Totsy_Sailthru_Block_Adminhtml_Widget_Grid_Column_Render_Hash 
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
        $val = str_replace("no_selection", "", $val);

        $url = Mage::getBaseUrl() .DS. 'dev' .DS. 'sailthrufeed' .DS. 'feed.php?params='. $val;

        $out = '<a href="'.$url.'">';
        $out .= $url;
        $out .= '</a>';

        return $out;
    }

}