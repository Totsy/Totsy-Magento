<?php

class Totsy_Promotions_Model_Mysql4_Banner extends Mage_Core_Model_Mysql4_Abstract
{
   
    protected function _construct(){
        $this->_init('promotions/banner', 'entity_id');
    }
    
    public function getBannersForHome(){

    	$select = $this->_getReadAdapter()
            ->select()
            ->from($this->getMainTable())
            ->where('start_at < NOW()')
            ->where('end_at > NOW()')
            ->where('is_active = ?', 1)
            ->where('at_home = ?', 1);

        return $this->_getReadAdapter()->fetchAll($select);
    }

    public function getBannersForEvetsOrPoruducts($page, $id){

    	$banners = array();

    	// prepere select to get lists of all active banners
    	$select = $this->_getReadAdapter()
            ->select()
            ->from($this->getMainTable())
            ->where('start_at < NOW()')
            ->where('end_at > NOW()')
            ->where('is_active = ?', 1);

        $result = $this->_getReadAdapter()->fetchAll($select);

        if (empty($result)){
        	return array();
        }

        // loop throught the list of active banners 
        // and try to collect banners
        foreach ($result as $entry){
        	$field = $entry['at_'.$page];
        	if (empty($field)){ continue; }
        	
        	$fields = explode(',', $field);
        	if (in_array($id, $fields)){
        		$banners[] = $entity_id;
        	}
        }

        return $counter;
    }

}
