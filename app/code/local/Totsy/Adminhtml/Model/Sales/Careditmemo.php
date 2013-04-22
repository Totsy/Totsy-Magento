<?php

class Totsy_Adminhtml_Model_Sales_Creditmemo extends Mage_Core_Model_Abstract {
    
    protected $_allowedExtentions = array('csv');
    protected $_orgFileName = null;
    protected $_fileName = null;

    public function import(){
        
        $this->uploadFile();

        return $this;
    }

    protected function _uploadFile(){
        
        if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
            $uploader = new Varien_File_Uploader('file');
            $uploader->setAllowedExtensions($this->_allowedExtentions);
            $uploader->setAllowRenameFiles(false);
            $uploader->setFilesDispersion(false);

            $this->_orgFileName = $_FILES['file']['name'];
            $uploader->save(Mage::getBaseDir('tmp'), $this->_orgFileName);

            $this->_fileName = preg_replace("/[\s]+/", '_', $this->_orgFileName);
            if (preg_match("/[\s]+/", $this->_orgFileName)){
                rename(
                    $Mage::getBaseDir('tmp').$this->_orgFileName, 
                    $Mage::getBaseDir('tmp').$this->_fileName
                );
            }
        }

        // Validate file name and extention
        if(is_null($this->_fileName)){
            throw new Exception('You have to upload a file in order to do credits import!');
        }
        if ( preg_match('/http|https/', $this->_fileName)){
            throw new Exception('File name can not contain http or https . Error value: ' . $this->_orgFileName);   
        }
        if ( !in_array(strtolower(substr($this->_fileName,-3)),$this->_allowedExtentions) ){
            throw new Exception('File type is not allowed to upload . Error value: ' . $this->_orgFileName);   
        }
    }

    protected function _processFileData(){

        $fh = fopen(Mage::getBaseDir('tmp').$this->_fileName,'r');
        if (!$fh){
            throw new Exception('Canot open uploaded file.');     
        }
        
        while (($row = fgetcsv($fh)) !== FALSE) {
            $this->_applyCredits($row);
        }

        fclose($fh);
    }

    protected function _applyCredits($row){
        $customer = Mage::getModel('customer/customer')
            ->loadByEmail($row[0]);

        if (!$customer->hasData()){
            return;
        }

    }
}