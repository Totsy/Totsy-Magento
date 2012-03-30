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
 * @category    Enterprise
 * @package     Enterprise_Invitation
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Customer registration form block
 *
 * @category   Enterprise
 * @package    Enterprise_Invitation
 */
class Enterprise_Invitation_Block_Customer_Form_Register extends Mage_Customer_Block_Form_Register
{
    /**
     * Retrieve form data
     *
     * @return Varien_Object
     */
    public function getFormData()
    {
        $data = $this->getData('form_data');
        if (is_null($data)) {
            $customerFormData = Mage::getSingleton('customer/session')->getCustomerFormData(true);
            $data = new Varien_Object($customerFormData);
            if (empty($customerFormData)) {
                $invitation = $this->getCustomerInvitation();
				//$data = $invitation;
                if ($invitation->getId()) {
                    // check, set invitation email
                    $data->setEmail($invitation->getEmail());
                }
            }
            $this->setData('form_data', $data);
        }
        return $data;
    }


    /**
     * Retrieve form posting url
     *
     * @return string
     */
    public function getPostActionUrl()
    {
    	if(!!Mage::registry('is_generic_invitation')){
    		return $this->getUrl('*/*/genericcreatepost', array('_current'=>true));
    	}else{
        	return $this->getUrl('*/*/createpost', array('_current'=>true));
    	}
    }

    /**
     * Retrieve customer invitation
     *
     * @return Enterprise_Invitation_Model_Invitation
     */
    public function getCustomerInvitation()
    {
        return Mage::registry('current_invitation');
    }
    
    //Yang
    public function getRegformData()
    {
       // $data = $this->getData('form_data');
        if (is_null($data)) {
            $customerFormData = Mage::getSingleton('customer/session')->getCustomerFormData(true);
            $data = new Varien_Object($customerFormData);
            if (empty($customerFormData)) {
                $invitation = $this->getCustomerInvitation();
				$data = $invitation;
            }
            $this->setData('form_data', $data);
        }
        return $data;
    }
    //
    
}
