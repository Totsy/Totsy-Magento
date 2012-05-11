<?php

class Harapartners_Service_Block_Rewrite_Adminhtml_Catalog_Category_Tab_General
    extends Mage_Adminhtml_Block_Catalog_Category_Tab_General
{
    protected $_hiddenFields = array('departments', 'ages', 'tags');

    protected function _setFieldset($attributes, $fieldset, $exclude=array())
    {
        die('tharsan');
        parent::_setFieldset($attributes, $fieldset, $exclude);

        foreach ($this->_hiddenFields as $fieldName) {
            if ($field = $fieldset->getForm()->getElement($fieldName)) {
                $field->setDisabled('disabled');
            }
        }

        return $this;
    }
}
