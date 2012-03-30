<?php

class Harapartners_Import_Model_Import extends Mage_Core_Model_Abstract
{
	//Harapartners_Import_Model_Import::IMPORT_STATUS_
	const IMPORT_STATUS_UPLOADED = 'uploaded';
	const IMPORT_STATUS_PROCESSING = 'processing';
	const IMPORT_STATUS_FINALIZING = 'finalizing';
	const IMPORT_STATUS_COMPLETE = 'complete';
	const IMPORT_STATUS_ERROR = 'error';
	
	
    public function _construct()
    {
        parent::_construct();
        $this->_init('import/import');
    }
    
 	//This is for updating 'created_at' and 'updated_at'
    protected function _beforeSave(){
    	//Magento Standard, always assume UTC timezone
    	if(!$this->getId()){
    		$this->setData('created_time', now());
    	}
    	$this->setData('update_time', now());
    	parent::_beforeSave();
    }
}