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

class Harapartners_Service_Block_Rewrite_Adminhtml_Customer_Edit_Tab_Account
    extends Mage_Adminhtml_Block_Customer_Edit_Tab_Account
{
    protected $_disabledFields = array('login_counter', 'purchase_counter');

    protected function _setFieldset($attributes, $fieldset, $exclude=array())
    {
        parent::_setFieldset($attributes, $fieldset, $exclude);

        foreach ($this->_disabledFields as $fieldName) {
            if ($field = $fieldset->getForm()->getElement($fieldName)) {
                $field->setDisabled('disabled');
            }
        }

        return $this;
    }
}
