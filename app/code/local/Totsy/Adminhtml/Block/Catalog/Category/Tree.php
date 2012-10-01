<?php
/**
 * @category    Totsy
 * @package     Totsy_Adminhtml_Block
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Adminhtml_Block_Catalog_Category_Tree
    extends Mage_Adminhtml_Block_Catalog_Category_Tree
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        //Harapartners, Jun do NOT allow adding root category, rename 'Add Subcategory'
        $this->unsetChild('add_root_button');
        if ($this->_isAllowedAction('add_category')) {
            $this->getChild('add_sub_button')->setData(
                'label',
                Mage::helper('catalog')->__('Add Category/Event')
            );
            $this->getChild('add_sub_button')->setData('disabled', 'disabled');
        } else {
            $this->unsetChild('add_sub_button');
        }

        return $this;
    }

    protected function _isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('catalog/categories/actions/' . $action);
    }
}
