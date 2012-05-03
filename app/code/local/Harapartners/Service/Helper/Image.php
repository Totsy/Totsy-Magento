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
 
class Harapartners_Service_Helper_Image extends Mage_Catalog_Helper_Image{
    
    public function loadImageFile($file, $imageType = 'image'){
        $this->_setModel(Mage::getModel('service/image'));
        $this->_getModel()->setDestinationSubdir($imageType); //Important to determine which placeholder to use
        if(is_file($file)){
            $this->_getModel()->setBaseFile( $file );
            $this->setImageFile($file);
        }else{
            //Note that Mage_Catalog_Model_Product_Image is the core image processor, use a dummy product to invoke the placeholder
            $this->setProduct(Mage::getModel('catalog/product'));
            $this->setImageFile(null);
        }
        return $this;
    }
    
}