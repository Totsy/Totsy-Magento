<?php
/**
 * {magecore_license_notice}
 *
 * @category   Oro
 * @package    Oro_Sales
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

/**
 * Edit Order Billing Info Block
 */
class Oro_Sales_Block_Order_Billing_Edit extends Mage_Core_Block_Template
{
    /**
     * @var Mage_Sales_Model_Order
     */
    protected $_order;

    /**
     * @var Mage_Customer_Model_Customer
     */
    protected $_customer;

    /**
     * @var Harapartners_Paymentfactory_Model_Mysql4_Profile_Collection
     */
    protected $_paymentCollection;

    /**
     * Payment method instance
     *
     * @var Harapartners_Paymentfactory_Model_Tokenize
     */
    protected $_paymentMethod;

    /**
     * Directory Country Collection
     *
     * @var Mage_Directory_Model_Resource_Country_Collection
     */
    protected $_countryCollection;

    /**
     * Returns Order instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if ($this->_order === null) {
            $this->_order = Mage::registry('current_order');
        }

        return $this->_order;
    }

    /**
     * Returns customer model
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if ($this->_customer === null) {
            /* @var $session Totsy_Customer_Model_Session */
            $session = Mage::getSingleton('customer/session');
            $this->_customer = $session->getCustomer();
        }

        return $this->_customer;
    }

    /**
     * Returns Payment collection
     *
     * @return Harapartners_Paymentfactory_Model_Mysql4_Profile_Collection
     */
    public function getPaymentCollection()
    {
        if ($this->_paymentCollection === null) {
            /* @var $profile Harapartners_Paymentfactory_Model_Profile */
            $profile = Mage::getModel('palorus/vault');
            $this->_paymentCollection = $profile->load($this->getCustomer()->getId(),'customer_id');
        }

        return $this->_paymentCollection;
    }

    /**
     * Returns Form Action URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('*/*/save', array(
            'order_id'  => $this->getOrder()->getId(),
        ));
    }

    /**
     * Checks if Customer has saved payment methods
     *
     * @return bool
     */
    public function hasPaymentMethods()
    {
        $result = false;
        /** @var $profile Harapartners_Paymentfactory_Model_Profile */
        foreach ($this->getPaymentCollection() as $profile) {
            if ($profile['vault_id']) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getPaymentAddressJson()
    {
        $result = array();
        /** @var $profile Harapartners_Paymentfactory_Model_Profile */
        foreach ($this->getPaymentCollection() as $profile) {
            if ($profile['vault_id']) {
                $result[$profile['vault_id']] = $profile['address_id'];
            }
        }
        return json_encode($result);
    }

    /**
     * @param Harapartners_Paymentfactory_Model_Profile $profile
     * @return Mage_Customer_Model_Address
     */
    public function getProfileAddress($profile)
    {
        /* @var $address Mage_Customer_Model_Address */
        $address = Mage::getModel('customer/address')->load($profile->getAddressId());

        return $address;
    }

    /**
     * Returns Customer full name by address
     *
     * @param Mage_Customer_Model_Address $address
     * @return string
     */
    public function getAddressCustomerName($address)
    {
        return sprintf('%s %s', $address->getFirstname(), $address->getLastname());
    }

    /**
     * Returns default payment method
     *
     * @return Harapartners_Paymentfactory_Model_Tokenize
     */
    public function getPaymentMethod()
    {
        if ($this->_paymentMethod === null) {
            $this->_paymentMethod = Mage::getModel('Litle_CreditCard_Model_PaymentLogic');
        }

        return $this->_paymentMethod;
    }

    /**
     * Returns Payment Config instance
     *
     * @return Harapartners_Paymentfactory_Model_Config
     */
    protected function _getPaymentConfig()
    {
        return Mage::getSingleton('paymentfactory/config');
    }

    /**
     * Returns list of available Credit Card types
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        $types  = $this->_getPaymentConfig()->getCcTypes();
        $method = $this->getPaymentMethod();
        $avail  = $method->getConfigData('cctypes');
        if ($avail) {
            $avail = explode(',', $avail);
            foreach ($types as $code => $name) {
                if (!in_array($code, $avail)) {
                    unset($types[$code]);
                }
            }
        }

        return $types;
    }

    /**
     * Returns list of credit card expire months
     *
     * @return array
     */
    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        if (is_null($months)) {
            $months = $this->_getPaymentConfig()->getMonths();
            $months = array(0 => $this->__('Month')) + $months;
            $this->setData('cc_months', $months);
        }

        return $months;
    }

    /**
     * Returns list of credit card expire years
     *
     * @return array
     */
    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if (is_null($years)) {
            $years = $this->_getPaymentConfig()->getYears();
            $years = array(0 => $this->__('Year')) + $years;
            $this->setData('cc_years', $years);
        }

        return $years;
    }

    /**
     * Check is Payment Method has verification configuration
     *
     * @return boolean
     */
    public function hasVerification()
    {
        if ($this->getPaymentMethod()) {
            $configData = $this->getPaymentMethod()->getConfigData('useccv');
            if (is_null($configData)) {
                return true;
            }

            return (bool)$configData;
        }

        return true;
    }

    /**
     * Returns Credit Card Type by code
     *
     * @param $shortCardType
     * @return string
     */
    public function getFullCcType($shortCardType)
    {
        switch ($shortCardType) {
            case 'AE':
                return 'American Express';
            case 'AX':
                return 'American Express';
            case 'VI':
                return 'Visa';
            case 'MC':
                return 'MasterCard';
            case 'DI':
                return 'Discover';
            default:
                return $shortCardType;
        }
    }

    /**
     * Check if customer has addresses
     *
     * @return int
     */
    public function customerHasAddresses()
    {
        return count($this->getCustomer()->getAddresses());
    }

    /**
     * Returns customer addresses select HTML
     *
     * @return string
     */
    public function getAddressesHtmlSelect()
    {
        $options = array();
        if ($this->customerHasAddresses()) {
            $options[] = array(
                'value' => '',
                'label' => 'New Address'
            );
        }

        /** @var $address Mage_Customer_Model_Address */
        foreach ($this->getCustomer()->getAddresses() as $address) {
            /* @var $profile Harapartners_Paymentfactory_Model_Profile */
            $profile = Mage::getModel('palorus/vault')->load($address->getId(), 'address_id');
            if ($profile->getId() && $profile->getIsDefault()) {
                continue;
            }

            $options[] = array(
                'value' => $address->getId(),
                'label' => $address->format('oneline')
            );
        }

        $addressId = $this->getOrder()->getBillingAddress()->getCustomerAddressId();

        /** @var $select Mage_Core_Block_Html_Select */
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName('billing_address_id')
            ->setId('billing-address-select')
            ->setClass('address-select')
            ->setValue($addressId)
            ->setOptions($options);

        return $select->getHtml();
    }

    public function getAddressesJson() {
        $json = array();
        $json[0] = array(
            'firstname' => $this->getCustomer()->getFirstname(),
            'lastname'  => $this->getCustomer()->getLastname(),
            'email'     => $this->getCustomer()->getEmail(),
        );
        foreach ($this->getCustomer()->getAddresses() as $address) {
            $addressData = $address->getData();
            $addressData [ 'email' ] = $this->getCustomer()->getEmail();
            $streetArray = explode( "\n", $addressData[ 'street' ] );
            $addressData[ 'street1' ] = $streetArray[0];
            $addressData[ 'street2' ] = isset( $streetArray[1] ) ? $streetArray[1] : '';
            $json[ $address->getId() ] = $addressData;
        }
        return json_encode( $json );
    }

    public function getCountryHtmlSelect()
    {
        $countryId  = Mage::helper('core')->getDefaultCountry();
        $select     = $this->getLayout()->createBlock('core/html_select')
            ->setName('billing[country_id]')
            ->setId('billing:country_id')
            ->setTitle(Mage::helper('checkout')->__('Country'))
            ->setClass('validate-select')
            ->setValue($countryId)
            ->setOptions($this->getCountryCollection()->toOptionArray());

        return $select->getHtml();
    }


    /**
     * Returns Country collection
     *
     * @return Mage_Directory_Model_Resource_Country_Collection
     */
    public function getCountryCollection()
    {
        if ($this->_countryCollection === null) {
            $this->_countryCollection = Mage::getSingleton('directory/country')->getResourceCollection()
                ->loadByStore();
        }

        return $this->_countryCollection;
    }
}
