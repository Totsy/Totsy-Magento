<?php

class Totsy_Promotions_Model_Banner extends Mage_Core_Model_Abstract {
    
    public static $path = 'import/banners/';
    protected $_allowedImageExtentions = array('jpg','jpeg','gif','png');
    protected $_maxWidth = 940;
    protected $_maxHeight = 199;

    protected function _construct(){
        $this->_init('promotions/banner');
    }

    public function getUrlPath(){
        return Mage::getBaseUrl('media'). self::$path;
    }

    public function getBanners(){
        $page = $this->getPageName();
        $pageId = $this->getPageId();
        $resource = $this->getResource();        

        $this->setCount(0);
        $this->setActiveBanners(array());

        if (empty($page)){
            return array();
        }

        if ($page=='home'){
            $banners = $resource->getBannersForHome();
        } else {
            $banners = $resource->getBannersForEvetsOrPoruducts($page,$id);
        }

        $this->setCount(count($banners));
        $this->setActiveBanners($banners);

        return $this; 
    }

    public function importData($data){
        //Type casting
        if(is_array($data)){
            $data = new Varien_Object($data);
        }
        if(!($data instanceof Varien_Object)){
            throw new Exception('Invalid type for data importing, Array or Varien_Object expected.');
        }

        //Forcefully overwrite existing data, certain data may need to be removed before this step
        $this->addData($data->getData());

        return $this;
    }

    protected function _beforeSave(){
        parent::_beforeSave();
        
        //For new object which does not specify 'created_at'
        if(!$this->getId() && !$this->getData('created_at')){
            $this->setData('created_at', now());
        }
        
        if(!$this->getData('is_active')){
            $this->setData('is_active', 0);
        }

        $this->setData('updated_at', now());

        $this->validate(); //Errors will be thrown as exceptions
        $this->uploadFile();

        return $this;
    }

    public function validate(){
        
        //Validate is_active
        if(!$this->hasIsActive()){
            throw new Exception('Active is required!');
        }
        if( strlen($this->getData('is_active'))>1){
            throw new Exception('Is Active can not be longer than 1 character. Error value: ' . $this->getData('name'));
        }
        if(!preg_match("/^[0-1]+$/", $this->getData('is_active'))){
            throw new Exception('Active must be boolean. Error value: ' . $this->getData('is_active'));
        }

        //Validate name
        if(!$this->getData('name')){
            throw new Exception('Name is required!');
        }
        if( strlen($this->getData('name'))>255){
            throw new Exception('Name can not be longer than 255 characters. Error value: ' . $this->getData('name'));
        }

        //Validate image
        if($this->getData('image')){

            if ( preg_match('/http|https/', $this->getData('image'))){
                throw new Exception('Image can not http or https . Error value: ' . $this->getData('image'));   
            }
            if ( !in_array(strtolower(substr($this->getData('image'),-3)),$this->_allowedImageExtentions) ){
                throw new Exception('Image type is not allowed to upload . Error value: ' . $this->getData('image'));   
            }
        }

        //Validate link
        if($this->getData('link')){

            if( strlen($this->getData('link'))>255){
                throw new Exception('Link can not be longer than 255 characters. Error value: ' . $this->getData('link'));
            }
            if ( preg_match('/http|https/', $this->getData('link'))){
                throw new Exception('Link can not http or https . Error value: ' . $this->getData('link')); 
            }
            if ( substr($this->getData('link'),0,1)!='/'){
                throw new Exception('Link must begin with \'/\'. Error value: ' . $this->getData('link'));  
            }
        }

        //Validate at_home
        if(!$this->hasAtHome()){
            throw new Exception('At Home Page is required!');
        }
        if( strlen($this->getData('at_home'))>1){
            throw new Exception('At Home can not be longer than 1 character. Error value: ' . $this->getData('name'));
        }
        if(!preg_match("/^[0|1]+$/", $this->getData('at_home'))){
            throw new Exception('At Home Page must be boolean. Error value: ' . $this->getData('is_active'));
        }

        //Validate at_events
        $this->_validateAtEvenetsAndproducts('at_events');
        
        //Validate at_products
        $this->_validateAtEvenetsAndproducts('at_products');
        
        // validate start_date
        if (!$this->getData('start_at')){
            throw new Exception('Start Date is required!');
        }
        try{
            $date = new DateTime($this->getData('start_at'));
        } catch (Exception $e){
            throw new Exception('Start date is invalid. '.$e->getMessage().'. Error value: ' . $this->getData('start_at')); 
        }

        // validate end_date
        if (!$this->getData('end_at')){
            throw new Exception('End Date is required!');
        }
        try{
            $date = new DateTime($this->getData('end_at'));
        } catch (Exception $e){
            throw new Exception('End date is invalid. '.$e->getMessage().'. Error value: ' . $this->getData('end_at')); 
        }

        return $this;
    }

    public function uploadFile(){
        
        if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {
            $uploader = new Varien_File_Uploader('image');
            $uploader->setAllowedExtensions($this->_allowedImageExtentions);
            $uploader->setAllowRenameFiles(false);
            $uploader->setFilesDispersion(false);
            $path = Mage::getBaseDir('media') . DS. self::$path;

            $bannerName = $_FILES['image']['name'];
            $uploader->save($path, $bannerName);

            if (preg_match("/[\s]+/", $bannerName)){
                $bannerNameNew = preg_replace("/[\s]+/", '_', $bannerName);
                rename($path.$bannerName, $path.$BannerNameNew.$ext);
                $bannerName = $bannerNameNew;
                unset($bannerNameNew);
            }
            $imageinfo = getimagesize($path.$bannerName);
            $type = image_type_to_extension($imageinfo[2]);
            $type = substr($type, 1);
            if (!in_array($type, $this->_allowedImageExtentions)){
                unlink($path.$bannerName);
                throw new Exception('Image type is not allowed to upload . Error value: ' . $type);   
            }

            if ($imageinfo[0]>$this->_maxWidth || $imageinfo[1]>$this->_maxHeight){
                unlink($path.$bannerName);
                throw new Exception('Image dimentions is too big. Allowed dimentions: ' . 
                    $this->_maxWidth . 'px by '.$this->_maxHeight . 'px.'.
                    ' Error value: ' . $imageinfo[0] . 'px  by ' . $imageinfo[1].'px');      
            }

            $newBannerName = md5_file($path.$bannerName);
            $ext = '.'.strtolower(substr($bannerName,-3));
            if (!file_exists($path.$newBannerName.$ext)){
                rename($path.$bannerName, $path.$newBannerName.$ext);
            }

            $this->setData('image',$newBannerName.$ext);
        }
    }

    protected function _validateAtEvenetsAndproducts($key){
        $at = array();

        if(!$this->getData($key)){
            return;
        }
        
        if (preg_match('/[^\d\n\,]/', $this->getData($key))){
            $title = str_replace('_', ' ', $this->getData($key));
            $title = ucwords($title);
            throw new Exception(
                $title.
                ' can contain only new line, ',' and any numeric characters. '.
                'Error value: ' . $this->getData($key)
            );
        }

        if ( preg_match("/[\n]/", $this->getData($key))){
            $at = explode("\n",$at);
        }
        if (empty($at)){
            $at = $this->getData($key);
        }
        $at = explode(',', $at);
        if (empty($at) || !is_array($at)){
            return;
        }
        $ats = $at;
        $at = array();
        foreach ($ats as $t){
            $t = trim($t);
            if (empty($t) || !is_numeric($t)){
                continue;
            }
            $at[] = $t;
        }
        if (empty($at)){
            return;
        }
        $this->setData($key,implode(',', $at));
    }
}