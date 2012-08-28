<?php
class Crown_Club_Helper_Data extends Mage_Core_Helper_Abstract {
	
	/**
	 * @since 0.1.0
	 * @var int
	 */
	private $_gracePeriod;
	
	/**
	 * @since 0.1.0
	 * @var Mage_Customer_Model_Group
	 */
	private $_nonClubCustomerGroup;
	
	/**
	 * @since 0.1.0
	 * @var Mage_Customer_Model_Group
	 */
	private $_clubCustomerGroup; 
	
	/**
	 * Check to see if the module has all required settings to run successfuly.
	 * @since 0.1.0
	 * @return boolean
	 */
	public function moduleSetupComplete() {
		$checks = array();
		$checks[] = is_numeric($this->getGracePeriod());
		$checks[] = $this->getNonClubCustomerGroup() instanceof Mage_Customer_Model_Group;
		$checks[] = $this->getClubCustomerGroup() instanceof Mage_Customer_Model_Group;
		$checks[] = $this->getNonClubCustomerGroup() != $this->getClubCustomerGroup();
		return !in_array(false, $checks, true);
	}
	
	/**
	 * Get the store value for the number of days in the grace period of an expired subscription
	 * @since 0.1.0
	 * @return int
	 */
	public function getGracePeriod() {
		if (!$this->_gracePeriod) {
			$this->_gracePeriod = abs((int)Mage::getStoreConfig('Crown_Club/clubgeneral/grace_period'));
		}
		return $this->_gracePeriod;
	}
	
	/**
	 * Get the customer group for non club members.
	 * @since 0.1.0
	 * @return Mage_Customer_Model_Group
	 */
	public function getNonClubCustomerGroup() {
		if (!$this->_nonClubCustomerGroup) {
			$groupId = Mage::getStoreConfig('Crown_Club/clubgeneral/nonclub_customer_group');
			$this->_nonClubCustomerGroup = Mage::getModel('customer/group')->load($groupId);
		}
		return $this->_nonClubCustomerGroup;
	}
	
	/**
	 * Get the customer group for club members.
	 * @since 0.1.0
	 * @return Mage_Customer_Model_Group
	 */
	public function getClubCustomerGroup() {
		if (!$this->_clubCustomerGroup) {
			$groupId = Mage::getStoreConfig('Crown_Club/clubgeneral/club_customer_group');
			$this->_clubCustomerGroup = Mage::getModel('customer/group')->load($groupId);
		}
		return $this->_clubCustomerGroup;
	}
	
	/**
	 * Checks to see if a customer is a club member
	 * @param Mage_Customer_Model_Customer $customer
	 * @since 0.1.0
	 * @return boolean
	 */
	public function isClubMember($customer) {
		$clubGroup = $this->getClubCustomerGroup();
		if ( $customer instanceof Mage_Customer_Model_Customer ) {
			$customerModel = $customer;
		} else {
			$customerModel = Mage::getModel('customer/customer')->load($customer);
		}
		return $clubGroup->getId() == $customer->getGroupId() && 1 == $customerModel->getData('is_club_member');
	}
}