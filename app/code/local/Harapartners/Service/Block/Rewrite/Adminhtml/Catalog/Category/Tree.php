<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

class Harapartners_Service_Block_Rewrite_Adminhtml_Catalog_Category_Tree extends Mage_Adminhtml_Block_Catalog_Category_Tree {

    protected function _prepareLayout() {
        parent::_prepareLayout();
        //Harapartners, Jun do NOT allow adding root category, rename 'Add Subcategory'
        $this->unsetChild('add_root_button');
        $this->getChild('add_sub_button')->setData('label', Mage::helper('catalog')->__('Add Category/Event'));
        $this->getChild('add_sub_button')->setData('disabled', 'disabled');
         $this->setChild('clear_expire',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('catalog')->__('Move Expired Events'),
                        'onclick'   => "setLocation('" . $this->getUrl('*/*/clearExpiredEvents', array('_current' => true, '_nosid' => true)) . "')"
                    ))
            );
        return $this;
    }

    public function getClearExpireButtonHtml()
    {
       // return $this->getChildHtml('clear_expire');
    }

}
