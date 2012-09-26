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
class Harapartners_Service_Block_Rewrite_Adminhtml_Catalog_Product_Edit_Tab_Attributes
    extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Attributes
{
    /**
     * Product attribute codes for attributes that should be set as read-only
     * in the product view.
     *
     * @var array
     */
    protected $_readonlyFields = array('sku', 'fulfillment_inventory', 'upc');

    protected function _setFieldset($attributes, $fieldset, $exclude = array())
    {
        parent::_setFieldset($attributes, $fieldset);
        foreach ($this->_readonlyFields as $readonlyFieldName) {
            if ($elt = $fieldset->getForm()->getElement($readonlyFieldName)) {
                $elt->setData('readonly', 'readonly');
            }
        }

        return $this;
    }
}
