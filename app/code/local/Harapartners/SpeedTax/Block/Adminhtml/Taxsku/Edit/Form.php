<?php 
class Harapartners_SpeedTax_Block_Adminhtml_Taxsku_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
 protected function _prepareForm() {
        $helper = Mage::helper('speedtax');

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $fieldset = $form->addFieldset('speedtax', array('legend'=>$helper->__('Pick events date range to map tax category and sku')));

        $fieldset->addField('start_at', 'date', array(
            'label'     => $helper->__('Start Date'),
            'name'      => 'start_at',
            'required'  => true,
            'time'      => true,
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'style'     => 'width: 140px;'
        ));

        $fieldset->addField('end_at', 'date', array(
            'label'     => $helper->__('End Date'),
            'name'      => 'end_at',
            'required'  => true,
            'time'      => true,
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'style'     => 'width: 140px;' 
        ));

        $fieldset->addField('ex_events', 'textarea', array(
            'label'     => $helper->__('Exclude Events'),
            'name'      => 'ex_events',
            'required'  => false,
            'note'      => 'Optional. Provide a list of events ids separeted by \',\' or new line'
        ));

        $fieldset->addField('ex_products', 'textarea', array(
            'label'     => $helper->__('Exclude Products'),
            'name'      => 'ex_products',
            'required'  => false,
            'note'      => 'Optional. Provide a list of products ids separeted by \',\' or new line' 
        ));

        if ( Mage::registry('speedtax_taxsku_form_data') ) {
            $form->setValues(Mage::registry('speedtax_taxsku_form_data'));
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
?>