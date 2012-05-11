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

class Harapartners_Service_Helper_Rewrite_Catalog_Category extends Mage_Catalog_Helper_Category {
    
    public function canShow($category) {
        if (is_int($category)) {
            $category = Mage::getModel('catalog/category')->load($category);
        }

        if (!$category->getId()) {
            return false;
        }

        if (!$category->getIsActive()) {
            return false;
        }
        
        if (!$category->isInRootCategoryList()) {
            return false;
        }
        
        //Harapartners, Jun/Yang, admin preview mode
        if(!!$category->getData('event_end_date')
        		&& !Mage::registry('admin_preview_mode')
        ){
            //Note this event_end_date is given by store timezone
            if(strtotime($category->getData('event_end_date')) < $this->_getStoreCurrentTime()){
                return false;
            }
        }

        return true;
    }
    
	public function getSecretKey($request, $controller = null, $action = null){
        $salt = Mage::getSingleton('core/session')->getData('secret_key_salt');
        $p = explode('/', trim($request->getOriginalPathInfo(), '/'));
        if (!$controller) {
            $controller = !empty($p[1]) ? $p[1] : $request->getControllerName();
        }
        if (!$action) {
            $action = !empty($p[2]) ? $p[2] : $request->getActionName();
        }
        $secret = $controller . $action . $salt;
        return Mage::helper('core')->getHash($secret);
    }
    
    protected function _getStoreCurrentTime(){
        $defaultTimezone = date_default_timezone_get();
        $mageTimezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);            
        date_default_timezone_set($mageTimezone);
        $timer = now();
        date_default_timezone_set($defaultTimezone);
        
        return strtotime($timer);
    }

}