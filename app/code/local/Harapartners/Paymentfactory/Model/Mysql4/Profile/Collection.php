<?php
/**
 * Harapartners Paymentfactory Profile Resource Collection
 *
 * @property Harapartners_Paymentfactory_Model_Profile[] $_items
 * @method Harapartners_Paymentfactory_Model_Profile[] getItems()
 * @method Harapartners_Paymentfactory_Model_Profile getItemById()
 * @method Harapartners_Paymentfactory_Model_Profile getFirstItem()
 * @method Harapartners_Paymentfactory_Model_Profile getLastItem()
 */
class Harapartners_Paymentfactory_Model_Mysql4_Profile_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Define Model for collection
     */
    public function _construct()
    {
        $this->_init('paymentfactory/profile');
    }

    /**
     * Adds filter by customer and loads collection
     *
     * @param int $customerId
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function loadByCustomerId($customerId)
    {
        if ($this->isLoaded()) {
            throw new Mage_Core_Exception('Cannot reload the collection.');
        }

        $this->addFieldToFilter('customer_id', $customerId);
        $this->load();

        return $this;
    }
}
