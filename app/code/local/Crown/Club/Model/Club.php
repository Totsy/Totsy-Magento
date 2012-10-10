<?php
class Crown_Club_Model_Club extends Mage_Core_Model_Abstract {

	/**
	 * (non-PHPdoc)
	 * @see Varien_Object::_construct()
	 */
	public function _construct() {
		parent::_construct ();
		$this->_init ( 'crownclub/club' );
	}

	/**
	 * Retrieve store model instance
	 *
	 * @return Mage_Core_Model_Store
	 */
	public function getStore() {
		$storeId = $this->getStoreId ();
		if ($storeId) {
			return Mage::app ()->getStore ( $storeId );
		}
		return Mage::app ()->getStore ();
	}

	/**
	 * Gets a list of expired membership accounts
	 * @since 0.1.0
	 * @return array
	 */
	public function getExpiredMembers() {
		if ( !$this->getData('expired_customers') ) {
			$helper = Mage::helper('crownclub');
			if (!$helper->moduleSetupComplete()) return array();
			$clubCustomerGroup = $helper->getClubCustomerGroup();

			$date = Zend_Date::now()->toString();

			$filterClubExpirationDate = array();
			$filterClubExpirationDate[] = array('attribute' => 'club_expiration_date', 'lteq' => $date, 'date' => true);
			$filterClubExpirationDate[] = array('attribute' => 'club_expiration_date', 'null' => 'null');

			$customers = Mage::getModel('customer/customer')->getCollection()
							->addAttributeToSelect('name')
							->addAttributeToSelect('club_expiration_date','left')
							->addAttributeToFilter($filterClubExpirationDate)
							->addAttributeToFilter('group_id', $clubCustomerGroup->getId());

			$this->setData('expired_customers', $customers);
		}

		return $this->getData('expired_customers');
	}

	/**
	 * Gets a list of expired membership accounts that are still within the grace period.
	 * @since 0.1.0
	 * @return array
	 */
	public function getExpiredMembersInGracePeriod() {
		if ( !$this->getData('expired_customers_in_grace_period') ) {
			$helper = Mage::helper('crownclub');
			if (!$helper->moduleSetupComplete()) return array();
			$clubCustomerGroup = $helper->getClubCustomerGroup();

			$gracePeriod = $helper->getGracePeriod() * -1;

			$date = Zend_Date::now()->addDay($gracePeriod)->toString();

			$filterClubExpirationDate = array();
			$filterClubExpirationDate[] = array('attribute' => 'club_expiration_date', 'lteq' => $date, 'date' => true);
			$filterClubExpirationDate[] = array('attribute' => 'club_expiration_date', 'null' => 'null');

			$customers = Mage::getModel('customer/customer')->getCollection()
							->addAttributeToSelect('name')
							->addAttributeToSelect('club_expiration_date','left')
							->addAttributeToFilter($filterClubExpirationDate)
							->addAttributeToFilter('group_id', $clubCustomerGroup->getId());

			$this->setData('expired_customers_in_grace_period', $customers);
		}

		return $this->getData('expired_customers_in_grace_period');
	}

	/**
	 * Gets a list of expired membership accounts that are past the grace period.
	 * @since 0.1.0
	 * @return array
	 */
	public function getExpiredMembersOutOfGracePeriod() {
		if ( !$this->getData('expired_customers_out_of_grace_period') ) {
			$helper = Mage::helper('crownclub');
			if (!$helper->moduleSetupComplete()) return array();
			$clubCustomerGroup = $helper->getClubCustomerGroup();

			$gracePeriod = $helper->getGracePeriod() * -1;

			$gracePeriodDate = Zend_Date::now()->addDay($gracePeriod)->toString();

			$date = Zend_Date::now()->toString();

			$filterClubExpirationDate = array();
			$filterClubExpirationDate[] = array('attribute' => 'club_expiration_date', 'gt' => $gracePeriodDate, 'date' => true);
			$filterClubExpirationDate[] = array('attribute' => 'club_expiration_date', 'lteq' => $date, 'date' => true);
			$filterClubExpirationDate[] = array('attribute' => 'club_expiration_date', 'null' => 'null');

			$customers = Mage::getModel('customer/customer')->getCollection()
							->addAttributeToSelect('name')
							->addAttributeToSelect('club_expiration_date','left')
							->addAttributeToFilter($filterClubExpirationDate)
							->addAttributeToFilter('group_id', $clubCustomerGroup->getId());

			$this->setData('expired_customers_out_of_grace_period', $customers);
		}

		return $this->getData('expired_customers_out_of_grace_period');
	}

	/**
	 * Adds a customer to the club customer group
	 * @param mixed Mage_Customer_Model_Customer|int $customer
	 * @uses event:crown_club_club_member_add
	 * @since 0.1.0
	 * @return Crown_Club_Model_Club
	 */
	public function addClubMember( $customer ) {
        /* @var $helper Crown_Club_Helper_Data */
		$helper = Mage::helper('crownclub');
		if (!$helper->moduleSetupComplete()) return $this;

		if ( $customer instanceof Mage_Customer_Model_Customer ) {
			$customerModel = $customer;
		} else {
			$customerModel = Mage::getModel('customer/customer')->load($customer);
		}

		$clubCustomerGroup = $helper->getClubCustomerGroup();

		if ($customerModel) {
			$customerModel->setIsClubMember(true);
			$customerModel->setGroupId($clubCustomerGroup->getId());
			$customerModel->save();

            try {
                $helper->setClubEmailList($customerModel);
            } catch (Exception $e) {
                Mage::logException($e);
            }

			$this->unsetData('expired_customers');
			$this->unsetData('expired_customers_in_grace_period');
			$this->unsetData('expired_customers_out_of_grace_period');
			Mage::dispatchEvent('crown_club_club_member_add',array($customerModel));
		}
		return $this;
	}

	/**
	 * Removes a customer from the club customer group
	 * @param mixed Mage_Customer_Model_Customer|int $customer
	 * @uses event:crown_club_club_member_remove
	 * @since 0.1.0
	 * @return Crown_Club_Model_Club
	 */
	public function removeClubMember( $customer ) {
        /* @var $helper Crown_Club_Helper_Data */
		$helper = Mage::helper('crownclub');
		if (!$helper->moduleSetupComplete()) return $this;

		if ( $customer instanceof Mage_Customer_Model_Customer ) {
			$customerModel = $customer;
		} else {
			$customerModel = Mage::getModel('customer/customer')->load($customer);
		}

		$clubNonCustomerGroup = $helper->getNonClubCustomerGroup();

		if ($customerModel) {
			$customerModel->setIsClubMember(false);
			$customerModel->setGroupId($clubNonCustomerGroup->getId());
			$customerModel->save();

            try {
                $helper->setNonClubEmailList($customerModel);
            } catch (Exception $e) {
                Mage::logException($e);
            }

			$this->unsetData('expired_customers');
			$this->unsetData('expired_customers_in_grace_period');
			$this->unsetData('expired_customers_out_of_grace_period');
			Mage::dispatchEvent('crown_club_club_member_remove',array($customerModel));
		}
		return $this;
	}

	/**
	 * Send email notifying customer that their subscription has been cancelled.
	 * @param Mage_Customer_Model_Customer $customer
	 * @return Crown_Club_Model_Club
	 */
	public function sendClubMembershipCancelledEmail($customer) {
		$storeId = $this->getStore()->getId();

		$mailer = Mage::getModel ( 'core/email_template_mailer' );
		$emailInfo = Mage::getModel ( 'core/email_info' );
		$emailInfo->addTo ( $customer->getEmail(), $customer->getName() );
		$mailer->addEmailInfo ( $emailInfo );

		// Set all required params and send emails
		$mailer->setSender('club');
		$mailer->setStoreId ( $storeId );
		$mailer->setTemplateId ( 'club_cancelled_email_template' );
		$mailer->setTemplateParams (
		array (
			'customer' => $customer->getName(),
		) );
		$mailer->send ();
		return $this;
	}

	/**
	 * Send email stating that payment has failed and their account will be cancelled.
	 * @param Mage_Customer_Model_Customer $customer
	 * @return Crown_Club_Model_Club
	 */
	public function sendClubMembershipPaymentFailedEmail($customer) {
		$storeId = $this->getStore()->getId();

		$mailer = Mage::getModel ( 'core/email_template_mailer' );
		$emailInfo = Mage::getModel ( 'core/email_info' );
		$emailInfo->addTo ( $customer->getEmail(), $customer->getName() );
		$mailer->addEmailInfo ( $emailInfo );

		// Set all required params and send emails
		$mailer->setSender('club');
		$mailer->setStoreId ( $storeId );
		$mailer->setTemplateId ( 'club_expired_email_template' );
		$mailer->setTemplateParams (
		array (
			'customer' => $customer->getName(),
		) );
		$mailer->send ();
		return $this;
	}
}