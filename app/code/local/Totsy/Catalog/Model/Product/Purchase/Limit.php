<?php 

class Totsy_Catalog_Model_Product_Purchase_Limit extends Mage_Core_Model_Abstract {

    protected $_allowedExtentions = array('csv');
    protected $_orgFileName = null;
    protected $_fileName = null;

    protected function _construct(){
        parent::_construct();
        $this->_init('totsy_catalog/product_purchase_limit', 'entity_id');
    }

    public function import(){
        $this->_uploadFile();
        $this->_processFileData();
        return $this;
    }

    public function checkPurchaseLimit($product,$futureQty=null){
        
        if(!$product->hasPurchaseMaxSaleQty()){
            // product doens't have purchase limit
            return;
        }

        $limit = $product->getPurchaseMaxSaleQty();
        $purchases = $this->getResource()
            ->getProductPurchasesByCustomer(
                $product->getId(),
                 Mage::getSingleton('customer/session')->getCustomer()->getId()
            );
        if (!is_null($futureQty)){
            $purchases+= (int) $futureQty;

            if ($purchases > $limit) {
                throw new Totsy_Catalog_Exception('Sorry, this product has a purchase limit of "'.$limit.'" per customer');
            }
            return;
        }

        if ($purchases >= $limit) {
            throw new Totsy_Catalog_Exception('Sorry, this product has a purchase limit of "'.$limit.'" per customer');
        }

        return;
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
        	$this->__removeFile();
            throw new Exception('You have to upload a file in order to set product putchase limit import!');
        }
        if ( preg_match('/http|https/', $this->_fileName) ){
        	$this->__removeFile();
            throw new Exception('File name can not contain http or https . Error value: ' . $this->_orgFileName);   
        }
        if ( !in_array(strtolower(substr($this->_fileName,-3)),$this->_allowedExtentions) ){
        	$this->__removeFile();
            throw new Exception('File type is not allowed to upload . Error value: ' . $this->_orgFileName);   
        }
    }

    protected function _processFileData(){

        $fh = fopen(Mage::getBaseDir('tmp').'/'.$this->_fileName,'r');
        if (!$fh){
        	$this->__removeFile();
            throw new Exception('Cannot open uploaded file.');     
        }
        
        while (($row = fgetcsv($fh)) !== FALSE) {
            if (empty($row[0]) || empty($row[1]) && !is_int($row[1]) ){
                continue;
            }

			$product = Mage::getModel('catalog/product')
				->loadByAttribute('sku',$row[0]);

	        if (empty($product) ){
	            continue;
	        }

	        $product->setPurchaseMaxSaleQty($row[1]);
	        $product->save();
        }

        fclose($fh);
        // remove already imported file
        $this->__removeFile();
    }

    private function __removeFile(){
    	if (file_exists(Mage::getBaseDir('tmp').'/'.$this->_fileName)){
    		unlink(Mage::getBaseDir('tmp').'/'.$this->_fileName);
    	}
    	if (file_exists(Mage::getBaseDir('tmp').'/'.$this->_orgFileName)){
    		unlink(Mage::getBaseDir('tmp').'/'.$this->_orgFileName);
    	}
    }
}
