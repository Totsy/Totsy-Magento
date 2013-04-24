<?php

/**
 * @category    Totsy
 * @package     Totsy_Promotions_Block_Adminhtml_Widget_Grid_Column_Render_Datetime 
 * @author      Slavik Koshelevskiy <skosh@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */

class Totsy_Promotions_Block_Adminhtml_Widget_Grid_Column_Render_Datetime 
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action {

    /**
     * Renders grid column
     *
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        return  $this->_getValue($row);

    }

}