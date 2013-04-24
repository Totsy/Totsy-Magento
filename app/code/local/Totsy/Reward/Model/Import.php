<?php

class Totsy_Reward_Model_Import extends Mage_Core_Model_Abstract {
    
    protected $_allowedExtentions = array('csv');
    protected $_orgFileName = null;
    protected $_fileName = null;

    public function import(){
        
        $this->_uploadFile();
        $this->_processFileData();

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
                    Mage::getBaseDir('tmp').'/'.$this->_orgFileName, 
                    Mage::getBaseDir('tmp').'/'.$this->_fileName
                );
            }
        }

        // Validate file name and extention
        if(is_null($this->_fileName)){
            throw new Exception('You have to upload a file in order to do credits import!');
        }
        if ( preg_match('/http|https/', $this->_fileName) ){
            throw new Exception('File name can not contain http or https . Error value: ' . $this->_orgFileName);   
        }
        if ( !in_array(strtolower(substr($this->_fileName,-3)),$this->_allowedExtentions) ){
            throw new Exception('File type is not allowed to upload . Error value: ' . $this->_orgFileName);   
        }
    }

    protected function _processFileData(){

        $fh = fopen(Mage::getBaseDir('tmp').'/'.$this->_fileName,'r');
        if (!$fh){
            throw new Exception('Canot open uploaded file.');     
        }
        
        while (($row = fgetcsv($fh)) !== FALSE) {
            if (empty($row[0]) || empty($row[1])){
                continue;
            }
            $this->_applyCredits($row);
        }

        fclose($fh);
    }

    protected function _applyCredits($row){
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(1)
            ->loadByEmail($row[0]);

        if (empty($customer) 
            || strtolower($customer->getEmail()) != strtolower($row[0]) 
        ){
            return;
        }

        $rewards = Mage::getModel('enterprise_reward/reward')->setCustomer($customer)->loadByCustomer();
        $points = $rewards->getPointsBalance() + $row[1];

        $history = Mage::getModel('enterprise_reward/reward_history')
            ->setRewardId($rewards->getRewardId())
            ->setWebsiteId($rewards->getWebSiteId())
            ->setStoreId(Mage::app()->getStore()->getStoreId())
            ->setEntity($rewards->getCustomerId())
            ->setPointsBalance($points)
            ->setPointsDelta($row[1])
            ->setComment('CS import credits. Csv file')
            ->setAdditionalData('a:1:{s:4:"rate";a:4:{s:6:"points";N;s:15:"currency_amount";N;s:9:"direction";N;s:13:"currency_code";s:3:"USD";}}')
            ->save();
        
        $rewards->setPointsBalance($points)->save();
    }
}