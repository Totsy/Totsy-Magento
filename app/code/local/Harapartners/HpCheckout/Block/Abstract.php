<?php
abstract class Harapartners_HpCheckout_Block_Abstract extends Mage_Core_Block_Template
{
    protected $_customer;
    protected $_customerSession;
    protected $_checkout;
    protected $_quote;
    protected $_countryCollection;
    protected $_regionCollection;
    protected $_addressesCollection;

    public function getCustomer()
    {
        if (empty($this->_customer)) {
            $this->_customer = $this->getCustomerSession()->getCustomer();
        }
        return $this->_customer;
    }
    
	public function getCustomerSession()
    {
        if (empty($this->_customerSession)) {
            $this->_customerSession = Mage::getSingleton('customer/session');
        }
        return $this->_customerSession;
    }

    public function getCheckout()
    {
        if (empty($this->_checkout)) {
            $this->_checkout = Mage::getSingleton('checkout/session');
        }
        return $this->_checkout;
    }

    public function getQuote()
    {
        if (empty($this->_quote)) {
            $this->_quote = $this->getCheckout()->getQuote();
        }
        return $this->_quote;
    }

    public function isCustomerLoggedIn()
    {
        return $this->getCustomerSession()->isLoggedIn();
    }

    public function getCountryCollection()
    {
        if (!$this->_countryCollection) {
            $this->_countryCollection = Mage::getSingleton('directory/country')->getResourceCollection()
                ->loadByStore();
        }
        return $this->_countryCollection;
    }

    public function getRegionCollection()
    {
        if (!$this->_regionCollection) {
            $this->_regionCollection = Mage::getModel('directory/region')->getResourceCollection()
                ->addCountryFilter($this->getAddress()->getCountryId())
                ->load();
        }
        return $this->_regionCollection;
    }
	
//    public function customerHasAddresses()
//    {
//        return count($this->getCustomer()->getAddresses());
//    }
//
//    
//    public function getAddressesHtmlSelect($type)
//    {
//        if ($this->isCustomerLoggedIn()) {
//            $options = array();
//            foreach ($this->getCustomer()->getAddresses() as $address) {
//                $options[] = array(
//                    'value' => $address->getId(),
//                    'label' => $address->format('oneline')
//                );
//            }
//
//            $addressId = $this->getAddress()->getCustomerAddressId();
//            if (empty($addressId)) {
//                if ($type=='billing') {
//                    $address = $this->getCustomer()->getPrimaryBillingAddress();
//                } else {
//                    $address = $this->getCustomer()->getPrimaryShippingAddress();
//                }
//                if ($address) {
//                    $addressId = $address->getId();
//                }
//            }
//
//            $select = $this->getLayout()->createBlock('core/html_select')
//                ->setName($type.'_address_id')
//                ->setId($type.'-address-select')
//                ->setClass('address-select')
//                ->setExtraParams('onchange="'.$type.'.newAddress(!this.value)"')
//                ->setValue($addressId)
//                ->setOptions($options);
//
//            $select->addOption('', Mage::helper('checkout')->__('New Address'));
//
//            return $select->getHtml();
//        }
//        return '';
//    }

    public function getCountryHtmlSelect($type)
    {
        $countryId = $this->getAddress()->getCountryId();
        if (is_null($countryId)) {
            $countryId = Mage::helper('core')->getDefaultCountry();
        }
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'[country_id]')
            ->setId($type.':country_id')
            ->setTitle(Mage::helper('checkout')->__('Country'))
            ->setClass('validate-select')
            ->setValue($countryId)
            ->setOptions($this->getCountryOptions());
        return $select->getHtml();
    }


    public function getRegionHtmlSelect($type)
    {
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'[region]')
            ->setId($type.':region')
            ->setTitle(Mage::helper('checkout')->__('State/Province'))
            ->setClass('required-entry validate-state')
            ->setValue($this->getAddress()->getRegionId())
            ->setOptions($this->getRegionCollection()->toOptionArray());

        return $select->getHtml();
    }

    public function getCountryOptions()
    {
        $options    = false;
        $useCache   = Mage::app()->useCache('config');
        if ($useCache) {
            $cacheId    = 'DIRECTORY_COUNTRY_SELECT_STORE_' . Mage::app()->getStore()->getCode();
            $cacheTags  = array('config');
            if ($optionsCache = Mage::app()->loadCache($cacheId)) {
                $options = unserialize($optionsCache);
            }
        }

        if ($options == false) {
            $options = $this->getCountryCollection()->toOptionArray();
            if ($useCache) {
                Mage::app()->saveCache(serialize($options), $cacheId, $cacheTags);
            }
        }
        return $options;
    }

    public function isShow()
    {
        return true;
    }
}
