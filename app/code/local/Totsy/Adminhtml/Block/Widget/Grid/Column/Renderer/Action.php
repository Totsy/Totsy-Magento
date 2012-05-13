<?php
/**
 * @category    Totsy
 * @package     Totsy_Adminhtml_Block_Widget_Grid_Column_Renderer
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    /**
     * Prepares action data for html render
     *
     * @param array $action
     * @param string $actionCaption
     * @param Varien_Object $row
     * @return Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
     */
    protected function _transformActionData(&$action, &$actionCaption, Varien_Object $row)
    {
        parent::_transformActionData($action, $actionCaption, $row);

        foreach ($action as $attribute => $value) {
            if ('caption_data_key' == $attribute) {
                $actionCaption = $row->getData($value);
                break;
            }
        }

        return $this;
    }
}
