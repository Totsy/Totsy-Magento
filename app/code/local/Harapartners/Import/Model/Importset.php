<?php

class Harapartners_Import_Model_Importset extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('import/importset');
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