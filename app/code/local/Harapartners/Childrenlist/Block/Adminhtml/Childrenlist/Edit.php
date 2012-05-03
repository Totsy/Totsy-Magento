<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Review edit form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Harapartners_Childrenlist_Block_Adminhtml_Childrenlist_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'childrenlist';
        $this->_controller = 'adminhtml_childrenlist';
        $this->_updateButton('save', 'label', Mage::helper('childrenlist')->__('Save Child Info'));
        $this->_updateButton('save', 'id', 'save_button');
        $this->_updateButton('delete', 'label', Mage::helper('childrenlist')->__('Delete Child Info'));
        
        if( $this->getRequest()->getParam('customerId', false) ) {
            $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('adminhtml/customer/edit', array('id' => $this->getRequest()->getParam('customerId', false))) .'\')' );
        }else {
            $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('adminhtml/customer/index') .'\')' );
        }
        Mage::getSingleton('adminhtml/session')->setEditPage(Mage::helper('core/url')->getCurrentUrl());
    }

    public function getHeaderText(){
            return Mage::helper('childrenlist')->__('Child');
    }
}