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
     * @since 0.6.0
     * @var Mage_Catalog_Model_Product
     */
    private $_clubProduct;

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
     * Gets the product model of the club subscription product
     * @since 0.6.0
     * @return Mage_Catalog_Model_Product|Mage_Core_Model_Abstract
     */
    public function getClubProduct() {
        if (!$this->_clubProduct) {
            $productId = abs((int)Mage::getStoreConfig('Crown_Club/clubgeneral/club_product_id'));
            $product = Mage::getModel('catalog/product')->load($productId);
            if ($product->getId()) {
                $this->_clubProduct = $product;
            }
        }
        return $this->_clubProduct;
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

    /**
     * Move a customer to the club email list
     * @param Mage_Customer_Model_Customer $customer
     * @since 0.3.0
     * @return void
     */
    public function setClubEmailList($customer) {
        if ( $customer instanceof Mage_Customer_Model_Customer ) {
            $customerModel = $customer;
        } else {
            $customerModel = Mage::getModel('customer/customer')->load($customer);
        }

        $sailthru = Mage::getSingleton('emailfactory/sailthruconfig')->getHandle();
        $defaultListName = Mage::getStoreConfig('sailthru_options/email/sailthru_news_list');
        $clubListName = Mage::getStoreConfig('sailthru_options/email/sailthru_club_list');

        if (empty($clubListName)) {
            Mage::throwException('No Sailthru club list set in admin. Customer not moved.');
            return;
        }
        // 1 Means add 0 Means remove
        $listArray = array(
            $defaultListName    => 0,
            $clubListName       => 1,
        );
        $this->_enqueueEmail($sailthru, $customerModel->getEmail(), array(), $listArray);
    }

    /**
     * Move a customer to the non club email list
     * @param Mage_Customer_Model_Customer $customer
     * @since 0.3.0
     * @return void
     */
    public function setNonClubEmailList($customer) {
        if ( $customer instanceof Mage_Customer_Model_Customer ) {
            $customerModel = $customer;
        } else {
            $customerModel = Mage::getModel('customer/customer')->load($customer);
        }
        $sailthru = Mage::getSingleton('emailfactory/sailthruconfig')->getHandle();
        $defaultListName = Mage::getStoreConfig('sailthru_options/email/sailthru_news_list');
        $clubListName = Mage::getStoreConfig('sailthru_options/email/sailthru_club_list');

        if (empty($clubListName)) {
            Mage::throwException('No Sailthru club list set in admin. Customer not removed.');
            return;
        }

        // 1 Means add 0 Means remove
        $listArray = array(
            $defaultListName    => 1,
            $clubListName       => 0,
        );
        $this->_enqueueEmail($sailthru, $customerModel->getEmail(), array(), $listArray);
    }

    /**
     * Enqueues an email api call
     *
     * @see Harapartners_EmailFactory_Model_Observer::_sendSailthruEmailWithMageExpection
     * @param string $email
     * @param array $vars
     * @param array $lists
     * @param array $templates
     * @param int   $verified
     * @param null  $optout
     * @param null  $send
     * @param array $send_vars
     * @since 0.3.0
     * @return void
     */
    protected function _enqueueEmail($email, $vars = array(), $lists = array(), $templates = array(), $verified = 0, $optout = null, $send = null, $send_vars = array()){
        try{
            $queueData = array(
                'call' => array(
                    'class' => 'emailfactory/sailthruconfig',
                    'methods' => array(
                        'getHandle',
                        'setEmail'
                    )
                ),
                'params' => array(
                    'setEmail' => compact('email', 'vars', 'lists', 'templates', 'verified', 'optout', 'send', 'send_vars')
                )
            );
            $queue = Mage::getModel('emailfactory/sailthruqueue');
            $queue->addToQueue($queueData);
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::throwException($e->getMessage());
        }
    }
}