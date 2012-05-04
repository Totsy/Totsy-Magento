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
 
class Harapartners_Service_Model_Image extends Mage_Catalog_Model_Product_Image {
    
    public function setBaseFile($file){
        
        if ((!$file) || (!file_exists($file))) {
            throw new Exception(Mage::helper('catalog')->__('Image file was not found.'));
        }

        $this->_baseFile = $file;

        //Write into a global cache folder
        $path = array(
            BP.DS.'media'.DS.'cache',
            Mage::app()->getStore()->getId()
        );
        if((!empty($this->_width)) || (!empty($this->_height)))
            $path[] = "{$this->_width}x{$this->_height}";

        // add misk params as a hash
        $miscParams = array(
                ($this->_keepAspectRatio  ? '' : 'non') . 'proportional',
                ($this->_keepFrame        ? '' : 'no')  . 'frame',
                ($this->_keepTransparency ? '' : 'no')  . 'transparency',
                ($this->_constrainOnly ? 'do' : 'not')  . 'constrainonly',
                $this->_rgbToString($this->_backgroundColor),
                'angle' . $this->_angle,
                'quality' . $this->_quality
        );

        // if has watermark add watermark params to hash
        if ($this->getWatermarkFile()) {
            $miscParams[] = $this->getWatermarkFile();
            $miscParams[] = $this->getWatermarkImageOpacity();
            $miscParams[] = $this->getWatermarkPosition();
            $miscParams[] = $this->getWatermarkWidth();
            $miscParams[] = $this->getWatermarkHeigth();
        }

        $path[] = md5(implode('_', $miscParams));

        //Truncate file name to build the right path
        $filePath = explode(DS, $file);
        // append prepared filename
        $this->_newFile = implode(DS, $path) . DS . $filePath[count($filePath)-1]; // the $file contains heading slash

        return $this;
    }
    
}