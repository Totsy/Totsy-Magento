<?php

/**
 * @category    Totsy
 * @package     Totsy_Promotions_Block_Adminhtml_Widget_Grid_Column_Render_Image
 * @author      Slavik Koshelevskiy <skosh@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */

class Totsy_Promotions_Block_Adminhtml_Widget_Grid_Column_Render_Image 
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

        $url = Mage::getBaseUrl('media') .DS. Totsy_Promotions_Model_Banner::$path . $val;

        $out = '<center>';
        $out .= "<img src=". $url ." width='200px' />";
        $out .= '</center>';

        return $out;
    }

}