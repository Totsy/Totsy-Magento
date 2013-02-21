<?php
class Crown_Club_Model_Sales_Service_Quote extends Mage_Sales_Model_Service_Quote {
	
	/**
     * Submit recurring payment profiles
     */
    protected function _submitRecurringPaymentProfiles()
    {
        $profiles = $this->_quote->prepareRecurringPaymentProfiles();
        foreach ($profiles as $profile) {
            if (!$profile->isValid()) {
                Mage::throwException($profile->getValidationErrors(true, true));
            } elseif ($profile->getData('is_club_profile')) {
            	// Cleanup
            	$profile->unsetData('is_club_profile');
            	$customerId = $this->_quote->getCustomerId();
            	Mage::getModel('crownclub/club')->addClubMember($customerId);
            }
            $profile->submit();
        }
        $this->_recurringPaymentProfiles = $profiles;
    }
}