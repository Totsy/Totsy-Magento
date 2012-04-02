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
class Harapartners_Fulfillmentfactory_Block_Adminhtml_Itemqueue_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
    
	protected function _prepareForm() {
		$yesno = Mage::getModel('adminhtml/system_config_source_yesno');

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));
        
        $infoFieldset = $form->addFieldset('info', array('legend'=>Mage::helper('fulfillmentfactory')->__('Item Queue Info')));
        
        $infoFieldset->addType('custom_link', 'Harapartners_Fulfillmentfactory_Block_Adminhtml_Itemqueue_Edit_Element_CustomLink');
        
        $infoFieldset->addField('order_item_id', 'label', array(
            'label'     => Mage::helper('fulfillmentfactory')->__('Order Item Id'),
            'name'      => 'order_item_id'
        ));
        
        $infoFieldset->addField('order_id', 'custom_link', array(
            'label'     => Mage::helper('fulfillmentfactory')->__('Order Id'),
            'name'      => 'order_id',
        	'href'		=> Mage::getModel('adminhtml/url')->getUrl('adminhtml/sales_order/view'),
        	'keyname'	=> 'order_id',
        	'note'		=> Mage::helper('fulfillmentfactory')->__('Click to view Order')
        ));
        
        $infoFieldset->addField('order_increment_id', 'label', array(
            'label'     => Mage::helper('fulfillmentfactory')->__('Order Increment Id'),
            'name'      => 'order_increment_id'
        ));
        
        $infoFieldset->addField('product_id', 'custom_link', array(
            'label'     => Mage::helper('fulfillmentfactory')->__('Product Id'),
            'name'      => 'product_id',
        	'href'		=>  Mage::getModel('adminhtml/url')->getUrl('adminhtml/catalog_product/edit'),
        	'keyname'	=> 'id',
        	'note'		=> Mage::helper('fulfillmentfactory')->__('Click to view Product')
        ));
        
        $infoFieldset->addField('name', 'label', array(
            'label'     => Mage::helper('fulfillmentfactory')->__('Product Name'),
            'name'      => 'name'
        ));
        
        $infoFieldset->addField('sku', 'label', array(
            'label'     => Mage::helper('fulfillmentfactory')->__('Product Sku'),
            'name'      => 'sku'
        ));
        
        $infoFieldset->addField('qty_ordered', 'label', array(
            'label'     => Mage::helper('fulfillmentfactory')->__('Ordered Qty'),
            'name'      => 'qty_ordered'
        ));
        
        $infoFieldset->addField('created_at', 'label', array(
            'label'     => Mage::helper('fulfillmentfactory')->__('Created Time'),
            'name'      => 'created_at'
        ));
        
        $infoFieldset->addField('updated_at', 'label', array(
            'label'     => Mage::helper('fulfillmentfactory')->__('Updated Time'),
            'name'      => 'updated_at'
        ));
        
        $queueFieldset = $form->addFieldset('itemqueue', array('legend'=>Mage::helper('fulfillmentfactory')->__('Item Queue Setting')));
        
        $queueFieldset->addField('fulfill_count', 'text', array(
            'label'     => Mage::helper('fulfillmentfactory')->__('Fulfill Count'),
            'name'      => 'fulfill_count',
            'class'     => 'validate-zero-or-greater',
        	'note'		=> Mage::helper('fulfillmentfactory')->__('Number of items for fulfillment')
        ));
        
        $queueFieldset->addField('status', 'select', array(
            'label'     => Mage::helper('fulfillmentfactory')->__('Status'),
            'name'      => 'status',
            'values'    => Mage::helper('fulfillmentfactory')->getItemqueueStatusDropdownOptionList(),
        	'note'		=> Mage::helper('fulfillmentfactory')->__('Current status for this item')
        ));
        
		if (Mage::registry('itemqueue_form_data')) {
            $form->setValues(Mage::registry('itemqueue_form_data'));
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}