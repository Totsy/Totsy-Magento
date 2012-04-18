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

class Harapartners_Service_Block_Rewrite_Page_Html_Head extends Mage_Page_Block_Html_Head {
    protected function _separateOtherHtmlHeadElements(&$lines, $itemIf, $itemType, $itemParams, $itemName, $itemThe) {
        $params = $itemParams ? ' ' . $itemParams : '';
        $href   = $itemName;
        switch ($itemType) {
            case 'rss':
                $lines[$itemIf]['other'][] = sprintf('<link href="%s"%s rel="alternate" type="application/rss+xml" />',
                    $href, $params
                );
                break;
            case 'link_rel':
                $lines[$itemIf]['other'][] = sprintf('<link%s href="%s" />', $params, $href);
                break;
                //sailthru//
            case 'js_inline':
                $lines[$itemIf]['other'][] = sprintf('<script type="text/javascript">%s</script>', $params);
                break;
            case 'meta':
                $lines[$itemIf]['other'][] = sprintf('<meta name="%s" content="%s" />', $itemName, htmlspecialchars($itemParams));
                break;
                //sailthru
        }
    }
    
    public function setTopnavKeywords(){
    	//$type = Mage::registry('attrtype');
		$type = Mage::app()->getRequest()->getParam('type');
		//$value = Mage::registry('attrvalue');
		$value = Mage::app()->getRequest()->getParam('value');
		$typeAttributes = Mage::getModel('catalog/product')->getResource()->getAttribute($type);
		$valueId = $typeAttributes->getSource()->getOptionId($value);
		$label = Mage::helper('catalog')->__($value);
		$this->setSailthruTitle($label);
		$label = strtolower(str_replace('-and-','-',str_replace(' ','-',$label)));
    	$this->setSailthruTags($label);
    }
    
    public function setEventTags(){
        $categoryId = (int) $this->getRequest()->getParam('id', false);
        if (!$categoryId) {
            return $this;
        }
		$category = Mage::getModel('catalog/category')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($categoryId);
        $label = $category->getName(); 
        $label = strtolower(str_replace(' ','-',$label));
        
    	$dept = $category->getDepartments();
        $deptArray = explode(',', $dept);
        $newDeptArray = array();
		$attrOptions = Mage::getModel('catalog/category')->getResource()->getAttribute('departments');
        foreach ($deptArray as $perdept){
        	//$attrText = $attrOptions->getSource()->getOptionText($perdept);
        	//$newDeptArray[] = 	$this->__($attrText);
        	$labeltemp = strtolower(str_replace('-and-','-',str_replace('_','-',$perdept)));
        	$newDeptArray[] = 	$labeltemp;
        }
        $deptStr = implode(', ' , $newDeptArray);
        if (count($deptArray)!=0){
        	$deptStr = ', '.$deptStr;
        }
        $age = $category->getAges();
        $ageArray = explode(',', $age);
		$newAgeArray = array();
		$ageAttrOptions = Mage::getModel('catalog/product')->getResource()->getAttribute('ages');
        foreach ($ageArray as $perage){
        	//$attrText = $ageAttrOptions->getSource()->getOptionText($perage);
        	$newAgeArray[] = 	$perage;
        }
        $ageStr = implode(', ' , $newAgeArray);
        if (count($ageArray)!=0){
        	$ageStr = ', '.$ageStr;
        }

        $label = trim($label.$deptStr.$ageStr , ',');
        
    	$this->setSailthruTags($label);
    }
    
    public function setEventSailthruTitle(){
        $categoryId = (int) $this->getRequest()->getParam('id', false);
        if (!$categoryId) {
            return $this;
        }
		$category = Mage::getModel('catalog/category')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($categoryId);
        $label = $category->getName(); 
        $label = strtolower(str_replace(' ','_',$label));
    	$this->setSailthruTitle($label);
    }
    
    public function setProductTags(){
        $productId = (int) $this->getRequest()->getParam('id', false);
        if (!$productId) {
            return $this;
        }
		$product = Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($productId);
        $label = $product->getName(); 
        $label = strtolower(str_replace(' ','-',$label));
        
        $dept = $product->getDepartments();
        $deptArray = explode(',', $dept);
        $newDeptArray = array();
		$attrOptions = Mage::getModel('catalog/product')->getResource()->getAttribute('departments');
        foreach ($deptArray as $perdept){
        	$attrText = $attrOptions->getSource()->getOptionText($perdept);
        	//$newDeptArray[] = 	$this->__($attrText);
        	$labeltemp = strtolower(str_replace('-and-','-',str_replace('_','-',$attrText)));
        	$newDeptArray[] = 	$labeltemp;
        }
        $deptStr = implode(', ' , $newDeptArray);
        if (count($deptArray)!=0){
        	$deptStr = ', '.$deptStr;
        }
        $age = $product->getAges();
        $ageArray = explode(',', $age);
		$newAgeArray = array();
		$ageAttrOptions = Mage::getModel('catalog/product')->getResource()->getAttribute('ages');
        foreach ($ageArray as $perage){
        	$attrText = $ageAttrOptions->getSource()->getOptionText($perage);
        	$newAgeArray[] = 	$this->__($attrText);
        }
        $ageStr = implode(', ' , $newAgeArray);
        if (count($ageArray)!=0){
        	$ageStr = ', '.$ageStr;
        }
        $label = trim($label.$deptStr.$ageStr , ',');
        //$label = $label.$deptStr.$ageStr;
    	$this->setSailthruTags($label);
    }
        
    public function setProductSailthruTitle(){
        $productId = (int) $this->getRequest()->getParam('id', false);
        if (!$productId) {
            return $this;
        }
		$product = Mage::getModel('catalog/product')->load($productId);
		$label = $product->getName();
    	$this->setSailthruTitle($label);
    }
}