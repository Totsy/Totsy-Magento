<?php
class Harapartners_PromotionFactory_Block_Adminhtml_Groupcoupon_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
    
	protected function _prepareForm() {
		
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post',
        	'enctype' => 'multipart/form-data'
        ));
        
        
        $fieldset = $form->addFieldset('rule', array('legend'=>Mage::helper('promotionfactory')->__('Rule Setting')));

        $fieldset->addField('name', 'label', array(
            'label'     => Mage::helper('promotionfactory')->__('Name'),
            'name'      => 'name',
            'required'  => true,
            'note'		=> Mage::helper('promotionfactory')->__('Rule name'),
        ));

       $fieldset->addField('import', 'text', array(
            'label'     => Mage::helper('promotionfactory')->__('Total Coupons'),
            'name'      => 'amount',
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}