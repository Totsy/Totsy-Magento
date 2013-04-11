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

			$customers = Mage::getModel('customer/customer')->getCollection()
							->addAttributeToSelect('name')
							->addAttributeToFilter('group_id', $clubCustomerGroup->getId());
            $customers->getSelect()->where('not exists (?)',new Zend_Db_Expr(
                "select profile_id from sales_recurring_profile srp where e.entity_id=srp.customer_id and srp.state='active'"
            ));
			$this->setData('expired_customers', $customers);
		}

		return $this->getData('expired_customers');
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
            $customerModel->setClubCreatedAt(strftime('%Y-%m-%d %H:%M:%S', time()));
			$customerModel->save();

            $this->sendClubMembershipWelcomeEmail($customerModel);

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
     * Send email welcoming a new TotsyPLUS member.
     * @param Mage_Customer_Model_Customer $customer
     * @return Crown_Club_Model_Club
     */
    public function sendClubMembershipWelcomeEmail($customer) {
        $store      = Mage::app()->getStore();
        $storeId    = $this->getStore()->getId();
        $email      = $customer->getEmail();
        $template   = Mage::getStoreConfig('Crown_Club/clubgeneral/club_welcome_email', $storeId);
        $templateId = Mage::getModel('core/email_template')->loadByCode($template)->getId();
        Mage::getModel('core/email_template')->sendTransactional(
            $templateId,
            "club",
            $email,
            NULL,
            array(
                "customer" => $customer,
                "store" => $store
            )
        );
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