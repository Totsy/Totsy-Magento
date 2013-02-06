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

        $error = false;

        try {
            switch($action) {
                case 'cancel':
                    if($profile->canCancel()) {
                        $profile->cancel();
                        $message = 'Cancelled recurring profile.';
                    } else {
                        $error = true;
                        $message = 'Could not cancel recurring profile.';
                    }
                    break;
                case 'activate':
                    if($profile->canActivate()) {
                        $profile->activate();
                        $message = 'Activated recurring profile.';
                    } else {
                        $error = true;
                        $message = 'Could not activate recurring profile.';
                    }
                    break;
                case 'suspend':
                    if($profile->canSuspend()) {
                        $profile->suspend();
                        $message = 'Suspended recurring profile.';
                    } else {
                        $error = true;
                        $message = 'Could not suspend recurring profile.';
                    }
                    break;
                default:
                    $error = true;
                    $message = 'Invalid action request.';
                    break;
            }
        } catch (Mage_Core_Exception $e) {
            $error = true;
            $message = 'Could not '.$action.' recurring profile.';
        }

        echo json_encode(array('error' => $error, 'message' => $message));


     /*
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
    */
    }
}