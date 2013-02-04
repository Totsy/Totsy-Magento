<?php
/**
 * @category    Totsy
 * @package     Totsy_Customer_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Customer_Model_Customer
    extends Mage_Customer_Model_Customer
{
    // Harapartners, yang, multistore sigin error control
    const EXCEPTION_INVALID_STORE_ACCOUNT = 5;

    // prevent any Customer objects from being deleted
    protected $_isDeleteable = false;

    /**
     * Harapartners, Jun
     * Important logic to handle legacy customers.
     *
     * @param string $password
     *
     * @return bool
     */
    public function validatePassword($password)
    {
        // legacy customer (sha1)
        if ($this->getData('legacy_customer')) {
            if (sha1($password) == $this->getPasswordHash()) {
                $this->setPassword($password); //Implicit save
                return true;
            } else {
                // super-legacy customer (hash_result:hash_salt, hashed with sha512)
                $hashData = explode(':', $this->getPasswordHash());
                if (count($hashData) == 2) {
                    $digest = $password . $hashData[1];
                    for ($i = 0; $i < 20; $i++) {
                        $digest = hash('sha512', $digest);
                    }

                    if ($digest == $hashData[0]) {
                        $this->setPassword($password); //Implicit save
                        return true;
                    } else {
                        return false;
                    }
                }

                return false;
            }

        // standard customer
        } else {
            $storedHash = $this->getPasswordHash();
            return ($storedHash == crypt($password, $storedHash));
        }
    }

    public function setPassword($password)
    {
        parent::setPassword($password);

        if ($this->getData('legacy_customer')) {
            $this->setData('legacy_customer', 0);
            $this->_getResource()->saveAttribute($this, 'legacy_customer');
        }

        return $this;
    }

    public function authenticate($login, $password, $reValidate = false)
    {
        $login = Mage::helper('customer')->sanitizeEmail($login);

        $this->loadByEmail($login);

        if ($this->getConfirmation() && $this->isConfirmationRequired()) {
            throw Mage::exception(
                'Mage_Core',
                Mage::helper('customer')->__('This account is not confirmed.'),
                self::EXCEPTION_EMAIL_NOT_CONFIRMED
            );
        }

        if (!$this->validatePassword($password)) {
            throw Mage::exception(
                'Mage_Core',
                Mage::helper('customer')->__('Invalid login or password.'),
                self::EXCEPTION_INVALID_EMAIL_OR_PASSWORD
            );
        }

        // Harapartners, yang, Add param for re-validate
        if (!$reValidate) {
            Mage::dispatchEvent(
                'customer_customer_authenticated',
                array(
                    'model'    => $this,
                    'password' => $password,
                )
            );
        }

        // Haraparters, yang, Set 15min validation time
        Mage::getSingleton('customer/session')
            ->setData('CUSTOMER_LAST_VALIDATION_TIME', now());

        return true;
    }

    /**
     * Haraparters, Jun: remove first name last name validation from registering.
     * Also validate password for against minimum criteria.
     *
     * @return array|bool
     */
    public function validate()
    {
        $errors = array();
        $customerHelper = Mage::helper('customer');

        if (!Zend_Validate::is($this->getEmail(), 'EmailAddress')) {
            $errors[] = $customerHelper->__('Invalid email address "%s".', $this->getEmail());
        }

        $password = $this->getPassword();
        if (!$this->getId() && !Zend_Validate::is($password, 'NotEmpty')) {
            $errors[] = $customerHelper->__('The password cannot be empty.');
        }
        if (strlen($password) && !Zend_Validate::is($password, 'StringLength', array(6))) {
            $errors[] = $customerHelper->__('The minimum password length is %s', 6);
        }
        $confirmation = $this->getConfirmation();
        if ($password != $confirmation) {
            $errors[] = $customerHelper->__('Please make sure your passwords match.');
        }

        if (strlen($password) && !preg_match('/\d/', $password)) {
            $errors[] = $customerHelper->__('The password must contain at least one number/digit.');
        }
        if (strlen($password) && !preg_match('/[a-zA-Z]/', $password)) {
            $errors[] = $customerHelper->__('The password must contain at least one alphanumeric character.');
        }

        $entityType = Mage::getSingleton('eav/config')->getEntityType('customer');
        $attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'dob');
        if ($attribute->getIsRequired() && '' == trim($this->getDob())) {
            $errors[] = $customerHelper->__('The Date of Birth is required.');
        }
        $attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'taxvat');
        if ($attribute->getIsRequired() && '' == trim($this->getTaxvat())) {
            $errors[] = $customerHelper->__('The TAX/VAT number is required.');
        }
        $attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'gender');
        if ($attribute->getIsRequired() && '' == trim($this->getGender())) {
            $errors[] = $customerHelper->__('Gender is required.');
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }


    //This is a very strange solution. Must check with the business logic
    //also there should also be a frontend validation so that the customer will be notified!

    protected function _beforeSave()
    {
        parent::_beforeSave();

        $storeId = $this->getStoreId();
        if ($storeId === null) {
            $this->setStoreId(Mage::app()->getStore()->getId());
        }

        $this->setEmail(Mage::helper('customer')->sanitizeEmail($this->getEmail()));

        // Harapartners, set default login count
        $loginCount = $this->getLoginCounter();
        if ($loginCount === null) {
            $this->setLoginCounter(0);
        }

        // Harapartners, add email md5 hash
        $this->setData('email_md5', md5($this->getEmail()));

        $this->getGroupId();
        return $this;
    }

    /**
     * Hash customer password, using bcrypt.
     *
     * @param   string $password
     * @param   int    $salt
     * @return  string
     */
    public function hashPassword($password, $salt = null)
    {
        // salt for bcrypt needs to be 22 base64 characters
        // (but just [./0-9A-Za-z]), see http://php.net/crypt
        $salt = substr(str_replace('+', '.', base64_encode(sha1(rand(), true))), 0, 22);

        return crypt($password, '$2a$12$' . $salt);
    }

    /**
     * Load customer by email
     *
     * @param   string $customerEmail
     * @return  Mage_Customer_Model_Customer
     */
    public function loadByEmail($customerEmail)
    {
        $customerEmail = Mage::helper('customer')->sanitizeEmail($customerEmail);
        return parent::loadByEmail($customerEmail);
    }

    public function fillNameWithBillingAddress($billingAddress) {
        if((!$this->getFirstname() || !$this->getLastname()) && $billingAddress) {
            $this->setFirstname($billingAddress->getFirstname())
                ->setLastname($billingAddress->getLastname())
                ->save();
        }
    }
}
