<?php
/**
 * Harapartners
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Harapartners License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.Harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@Harapartners.com so we can send you a copy immediately.
 *
 */
class Harapartners_Childrenlist_Block_Adminhtml_Childrenlist_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
	
    protected function _prepareForm() {
    	
    	//Initialization
    	$childId = $this->getRequest()->getParam('id');
    	$child = Mage::getModel('childrenlist/child');
    	if(!!$childId){
    		$child->load($childId);
    	}
    	
    	$customerId = $this->getRequest()->getParam('customerId');
    	if(!$customerId && $child->getId()){
    		$customerId = $child->getCustomerId();
    	}
    	$customer = Mage::getModel('customer/customer')->load($customerId);

		//Form preparation
    	$form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method'    => 'post'
        ));

        $fieldset = $form->addFieldset('child_details', array('legend' => Mage::helper('childrenlist')->__('Child Details'), 'class' => 'fieldset-wide'));

        if ($customer->getId()) {
            $customerText = Mage::helper('childrenlist')->__('<a href="%1$s" >%2$s %3$s</a> <a href="mailto:%4$s">(%4$s)</a>',
                $this->getUrl('adminhtml/customer/edit', array('id' => $customer->getId(), 'active_tab'=>'childlist')),
                $this->htmlEscape($customer->getFirstname()),
                $this->htmlEscape($customer->getLastname()),
                $this->htmlEscape($customer->getEmail()));
        } else {
            if (is_null($child->getCustomerId())) {
                $customerText = Mage::helper('childrenlist')->__('Guest');
            } elseif ($child->getCustomerId() == 0) {
                $customerText = Mage::helper('childrenlist')->__('Administrator');
            }
        }
		
        if (isset($childId)){
	        $fieldset->addField('child_id', 'text', array(
	            'value'      => $childId,
	        	'name'      => 'child_id',
	        	'style'   => "display:none",
	        	'readonly' => true
	        ));
        }
        
        $fieldset->addField('customer', 'note', array(
            'label'     => Mage::helper('childrenlist')->__('Customer Infomation'),
            'text'      => $customerText,
        ));
        
        $fieldset->addField('child_name', 'text', array(
            'label'     => Mage::helper('childrenlist')->__('Child Name'),
            'required'  => true,
            'name'      => 'child_name'
        ));
        
        $child_gender = Harapartners_Childrenlist_Model_Child::getChildGenderLabels();
        
        $fieldset->addField('child_gender', 'select', array(
            'label'     => Mage::helper('childrenlist')->__('Gender'),
            'name'      => 'child_gender',
            'values'    => Mage::helper('childrenlist')->translateArray($child_gender),
        ));

        $child_customer_relationship = Harapartners_Childrenlist_Model_Child::getChildRelationshipLabels();
        
        $fieldset->addField('child_customer_relationship', 'select', array(
            'label'     => Mage::helper('childrenlist')->__('Relationship'),
            'name'      => 'child_customer_relationship',
            'values'    => Mage::helper('childrenlist')->translateArray($child_customer_relationship),
        ));
        

        $fieldset->addField('child_birthday', 'date', array(
            'label'     => Mage::helper('childrenlist')->__('Child\'s Birthday'),
            'name'      => 'child_birthday',
            'tabindex' => 1,
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
        ));

        $fieldset->addField('additional_data', 'textarea', array(
            'label'     => Mage::helper('childrenlist')->__('Additional Data (JSON string)'),
            'name'      => 'additional_data',
            'style'     => 'height:24em;',
        	'after_element_html' => '<small>The Additional Data Must be a valid JSON string or Blank</small>',
        ));
        
        $form->setUseContainer(true);
        
        //prepare form data 
        //1)Try to get from session (for failed activities)
        //2)Try to load if ID is available
        $childData = array();
        if(Mage::getSingleton('adminhtml/session')->hasChildEditFormData()){
        	$childData = Mage::getSingleton('adminhtml/session')->getChildEditFormData();
        }
        if($child->getId()){
        	$childData = $child->getData();
        }
        $form->setValues($childData);
        
        $fieldset->addField('customer_id', 'text', array(
            'value'      => $customerId,
        	'name'      => 'customer_id',
        	'style'   => "display:none",
        	'readonly' => true
        ));
        
        
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
