<?php
/**
 *
 * @category      Crown
 * @package       Crown_Club
 * @since         0.6.0
 */
class Crown_Club_Helper_Earlyaccess extends Mage_Core_Helper_Abstract {

    /**
     * @var int
     * @since 0.6.0
     */
    private $_early_access_time;

    /**
     * Get the number of seconds a club member is allowed to see sales early.
     * @since 0.6.0
     * @return number
     */
    public function getEarlyAccessTime() {
        if (!$this->_early_access_time) {
            $this->_early_access_time = abs((int)Mage::getStoreConfig('Crown_Club/clubearlyaccess/early_access_time'));
        }
        return $this->_early_access_time;
    }

    /**
     *
     * @param Mage_Catalog_Model_Category $category
     * @since 0.6.0
     * @return bool
     */
    public function isEventAvailableForMembers ($category) {
        if(!Mage::getStoreConfig('Crown_Club/clubgeneral/enable')) {
            return false;
        }
        $mageTimezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);

        $currentDate = new Zend_Date();
        $currentDate->setTimezone($mageTimezone);

        $earlyAccessStartDate = $this->getEventTimeUntilAvailableForMembers($category);

        if ( strtotime( $currentDate->toString() ) >= strtotime( $earlyAccessStartDate->toString() ) ) {
            return true;
        }
        return false;

    }

    /**
     *
     * @param Mage_Catalog_Model_Category $category
     * @since 0.6.0
     * @return Zend_Date
     */
    public function getEventTimeUntilAvailableForMembers($category) {
        $earlyAccessTimeWindow = $this->getEarlyAccessTime();
        $startDate = new Zend_Date($category->getEventStartDate()
            ,'yyyy-MM-dd hh:mm:ss');
        $earlyAccessStartDate = clone $startDate;
        $earlyAccessStartDate->subSecond($earlyAccessTimeWindow);
        return $earlyAccessStartDate;
    }

}