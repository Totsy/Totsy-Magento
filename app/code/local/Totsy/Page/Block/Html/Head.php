<?php
/**
 * @category    Totsy
 * @package     Totsy_Page_Block_Html
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Page_Block_Html_Head extends Mage_Page_Block_Html_Head
{
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
        $type = Mage::registry('attrtype');
        $value = Mage::registry('attrvalue');
//        $type = Mage::app()->getRequest()->getParam('type');
//        $value = Mage::app()->getRequest()->getParam('value');
        $attrObj = Mage::getModel('catalog/product')->getResource()->getAttribute($type);
        $codeFormateLabel = $attrObj->getSource()->getOptionText($value);
        $label = Mage::helper('catalog')->__($codeFormateLabel);
        //$label = strtolower(str_replace('-and-','-',str_replace(' ','-',$label)));
        $this->setSailthruTags($codeFormateLabel);
        $this->setSailthruTitle($label);

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
            //$newDeptArray[] =     $this->__($attrText);
            $labeltemp = strtolower(str_replace('-and-','-',str_replace('_','-',$perdept)));
            $newDeptArray[] =     $labeltemp;
        }
        $deptStr = implode(', ' , $newDeptArray);
        if (!empty($dept)){
            $deptStr = ', '.$deptStr;
        }
        $age = $category->getAges();
        $ageArray = explode(',', $age);
        $newAgeArray = array();
        $ageAttrOptions = Mage::getModel('catalog/category')->getResource()->getAttribute('ages');
        foreach ($ageArray as $perage){
            //$attrText = $ageAttrOptions->getSource()->getOptionText($perage);
            $newAgeArray[] = $perage;
        }
        $ageStr = implode(', ' , $newAgeArray);
        if (!empty($age)){
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
        //$label = strtolower(str_replace(' ','-',$label));
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
        $TagsArray = array();
        $TagsArray[] = $label;

        $dept = $product->getDepartments();
        $deptArray = explode(',', $dept);
        $newDeptArray = array();
        $attrOptions = Mage::getModel('catalog/product')->getResource()->getAttribute('departments');
        foreach ($deptArray as $perdept){
            $attrText = $attrOptions->getSource()->getOptionText($perdept);
            //$newDeptArray[] =     $this->__($attrText);
            $labeltemp = strtolower(str_replace('-and-','-',str_replace('_','-',$attrText)));
            $newDeptArray[] =     $labeltemp;
            $TagsArray[] = $labeltemp;
        }
        $deptStr = implode(', ' , $newDeptArray);
        /*if (!empty($dept)){
            $deptStr = ', '.$deptStr;
        }*/
        $age = $product->getAges();
        $ageArray = explode(',', $age);
        $newAgeArray = array();
        $ageAttrOptions = Mage::getModel('catalog/product')->getResource()->getAttribute('ages');
        foreach ($ageArray as $perage){
            $attrText = $ageAttrOptions->getSource()->getOptionText($perage);
            //$newAgeArray[] =     $this->__($attrText);
            $newAgeArray[] =     $attrText;
            $TagsArray[] = $attrText;
        }
        $ageStr = implode(', ' , $newAgeArray);
        /*if (count($age)>1){
            $ageStr = ', '.$ageStr;
        }*/
        $Tags = implode(', ',$TagsArray);
        //$label = trim($label.$deptStr.$ageStr , ',');
        //$label = $label.$deptStr.$ageStr;
        $this->setSailthruTags($Tags);
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

    /**
     * Merge static and skin files of the same format into 1 set of HEAD
     * directives or even into 1 directive
     *
     * Will attempt to merge into 1 directive, if merging callback is provided.
     * In this case it will generate filenames, rather than render urls.
     * The merger callback is responsible for checking whether files exist,
     * merging them and giving result URL
     *
     * @param string $format - HTML element format for sprintf('<element src="%s"%s />', $src, $params)
     * @param array $staticItems - array of relative names of static items to
     *                             be grabbed from js/ folder
     * @param array $skinItems - array of relative names of skin items to be
     *                           found in skins according to design config
     * @param callback $mergeCallback
     * @return string
     */
    protected function &_prepareStaticAndSkinElements(
        $format,
        array $staticItems,
        array $skinItems,
        $mergeCallback = null
    ) {
        $designPackage = Mage::getDesign();
        $baseJsUrl = Mage::getBaseUrl('js');
        $items = array();
        if ($mergeCallback && !is_callable($mergeCallback)) {
            $mergeCallback = null;
        }

        // get static files from the js folder, no need in lookups
        foreach ($staticItems as $params => $rows) {
            foreach ($rows as $name) {
                $items[$params][] = $mergeCallback
                    ? Mage::getBaseDir() . DS . 'js' . DS . $name
                    : $baseJsUrl . $name;
            }
        }

        // lookup each file basing on current theme configuration
        foreach ($skinItems as $params => $rows) {
            foreach ($rows as $name) {
                $items[$params][] = $mergeCallback
                    ? $designPackage->getFilename($name, array('_type' => 'skin'))
                    : $designPackage->getSkinUrl($name, array());;
            }
        }

        $html = '';
        foreach ($items as $params => $rows) {
            // attempt to merge
            $mergedUrl = false;
            if ($mergeCallback) {
                $mergedUrl = call_user_func($mergeCallback, $rows);
            }
            // render elements
            $params = trim($params);
            $params = $params ? ' ' . $params : '';
            if ($mergedUrl) {
                $html .= sprintf($format, $mergedUrl, $params);
            } else {
                foreach ($rows as $src) {
                    // determine the last modified timestamp of the asset file
                    $filename = substr($src, strpos($src, '/', 8)+1);
                    if (file_exists($filename)) {
                        $src .= "?v=" . filemtime($filename);
                    }

                    $html .= sprintf($format, $src, $params);
                }
            }
        }
        return $html;
    }
}
