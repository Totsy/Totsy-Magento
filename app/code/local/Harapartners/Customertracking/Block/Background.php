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

class Harapartners_Customertracking_Block_Background extends Mage_Core_Block_Template {
    
    public function getBackroundImagesJsonArray(){
        $dir = BP . DS . 'skin'. DS .'frontend'. DS .'enterprise'. DS .'harapartners'. DS .'images'. DS .'login'. DS;
        $images = scandir($dir);
        unset($images[0]);
        unset($images[1]);
        $imageArray = array();
        $keyword = $this->getKeyword();
        foreach ($images as $image){
            $imageArray[] = $this->getSkinUrl().'images/login/'.$image;
        }
        $jsonArray = array(
            'keyword' => $keyword,
            'dir'      => $this->getSkinUrl().'images/login/',
            'images'  => $imageArray,
        );
        return json_encode($jsonArray);    
    }
    
    public function getKeyword(){
        $keywordCookieName = Mage::helper('affiliate')->getKeywordCookieName();
        $keyword = Mage::getModel('core/cookie')->get($keywordCookieName);
        if (!!$keyword) {
            return $keyword;
        }
        return false;        
    }
    
}