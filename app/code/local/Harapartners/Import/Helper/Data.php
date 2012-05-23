<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

class Harapartners_Import_Helper_Data extends Mage_Core_Helper_Abstract {
    
    public function getFormActionTypeArray(){
        return array(
                   array('label' => 'Process Immediately and Index', 'value' => Harapartners_Import_Model_Import::ACTION_TYPE_PROCESS_IMMEDIATELY_AND_INDEX),
//                   array('label' => 'Process Immediately without Index', 'value' => Harapartners_Import_Model_Import::ACTION_TYPE_PROCESS_IMMEDIATELY),
//                   array('label' => 'Pending', 'value' => Harapartners_Import_Model_Import::ACTION_TYPE_PENDING),
//                   array('label' => 'Validation Only', 'value' => Harapartners_Import_Model_Import::ACTION_TYPE_VALIDATION_ONLY)
           );
    }
    
    public function getGridStatusArray(){
        return array(
                Harapartners_Import_Model_Import::IMPORT_STATUS_COMPLETE => 'Complete', 
                Harapartners_Import_Model_Import::IMPORT_STATUS_ERROR => 'Error',
                Harapartners_Import_Model_Import::IMPORT_STATUS_FINALIZING => 'Finalizing',
                Harapartners_Import_Model_Import::IMPORT_STATUS_PROCESSING => 'Processing',
                Harapartners_Import_Model_Import::IMPORT_STATUS_UPLOADED => 'Uploaded'
        );
    }
    
}