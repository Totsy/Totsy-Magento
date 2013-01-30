<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ryan.street
 * Date: 1/18/13
 * Time: 5:39 PM
 * To change this template use File | Settings | File Templates.
 */
class Crown_Club_Profile_RecurringController extends Mage_Core_Controller_Front_Action {

    public function updateAction() {

        $profile_id = $this->getRequest()->getParam('profile');

        $action = $this->getRequest()->getParam('action');

        $profile = Mage::getModel('sales/recurring_profile')->load($profile_id);
        if (!$profile->getId()) {
            Mage::throwException($this->__('Specified profile does not exist.'));
        }

        if(!$action) {
            Mage::throwException($this->__('Please specify an action. '));
        }

        try {
            switch($action) {
                case 'activate':
                    $profile->activate();
                    break;
                case 'suspend':
                    $profile->suspend();
                    break;
                default:
                    Mage::throwException($this->__('Invalid Action'));
                    break;
            }
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
        }

    }
}