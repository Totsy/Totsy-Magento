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
 * Customer account form block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Customer_Edit_Tab_Invitations extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct()
    {
        $this->setTemplate('customer/tab/invitations.phtml');
        parent::__construct();
    }
    public function getCustomer()
    {
        if (!$this->_customer) {
            $this->_customer = Mage::registry('current_customer');
        }
        return $this->_customer;
    }

    public function getTabLabel()
    {
        return Mage::helper('customer')->__('Invitations');
    }

    public function getTabTitle()
    {
        return Mage::helper('customer')->__('Invitations');
    }

    public function canShowTab()
    {
        if (Mage::registry('current_customer')->getId()) {
            return true;
        }
        return false;
    }

    public function isHidden()
    {
        if (Mage::registry('current_customer')->getId()) {
            return false;
        }
        return true;
    }

    public function getInvitedBy() {
        $invitation = Mage::getModel('enterprise_invitation/invitation')->load($this->getCustomer()->getId(), 'referral_id');

        if($invitation->getData()) {
            $inviter = Mage::getModel('customer/customer')->load($invitation->getCustomerId());
            $username = $inviter->getFirstname() . " " . $inviter->getLastname();
            return "<a target='_blank' href='" . $this->getUrl("*/*/edit", array('id' => $invitation->getCustomerId())) . "'>" . $username . "</a>";
        }

        return 'N/A';
    }

    public function getInvitationInfo() {
        $invitation = Mage::getModel('enterprise_invitation/invitation')->load($this->getCustomer()->getId(), 'referral_id');

        if($invitation->getData()) {
            return $invitation;
        }

        return null;
    }

    public function getSentInvitations() {
        $invitations = Mage::getModel('enterprise_invitation/invitation')->getCollection();
        $invitations->getSelect()->where('customer_id=' . $this->getCustomer()->getId());
        
        if($invitations->getData()) {
            return $invitations->getData();
        }
        
        return 'N/A';
    }

    public function getInviteeFirstPurchaseDate($order_increment_id) {
        $order = Mage::getModel('sales/order')->load($order_increment_id, 'increment_id');
        
        if($order) {
            return $order->getData('created_at');
        }
        
        return null;
    }
}
