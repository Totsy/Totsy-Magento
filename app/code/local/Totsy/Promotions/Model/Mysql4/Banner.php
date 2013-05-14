<?php

class Totsy_Promotions_Model_Mysql4_Banner extends Mage_Core_Model_Mysql4_Abstract
{
   
    protected function _construct(){
        $this->_init('promotions/banner', 'entity_id');
    }
    
    public function getBannersForHome(){
        $now = $this->storeTime();
        $select = $this->_getReadAdapter()
            ->select()
            ->from($this->getMainTable())
            ->where('start_at < ?',$now)
            ->where('end_at > ?',$now)
            ->where('is_active = ?', 1)
            ->where('at_home = ?', 1);
        return $this->_getReadAdapter()->fetchAll($select);
    }

    public function getBannersForEvetsOrPoruducts($page, $id){

        $banners = array();
        $now = $this->storeTime();

        // prepere select to get lists of all active banners
        $select = $this->_getReadAdapter()
            ->select()
            ->from($this->getMainTable())
            ->where('start_at < ?',$now)
            ->where('end_at > ?',$now)
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

    private function storeTime($unixTimestamp = false){
        $defaultTimezone = date_default_timezone_get();
        $mageTimezone = Mage::getStoreConfig(
            Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE
        );
        date_default_timezone_set($mageTimezone);
        $time = time();
        if ($unixTimestamp == false){
            $time = date('Y-m-d H:i:s');
        }
        date_default_timezone_set($defaultTimezone);
        return $time;
    }
}
