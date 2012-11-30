<?php
class Harapartners_Fulfillmentfactory_Block_Adminhtml_Fulfillment_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
    	$helper = Mage::helper('fulfillmentfactory');

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $infoFieldset = $form->addFieldset('info', array('legend'=>Mage::helper('fulfillmentfactory')->__('Change Event Fulfillment')));

        $infoFieldset->addField ( 'fulfillment_type', 'select', array (
             'label' => $helper->__ ( 'Fulfillment Type' ),
             'required' => true,
             'name' => 'fulfillment_type',
             'values' => $helper->getAllFulfillmentTypesArray (),
        ) );

        $form->setValues(array());

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}