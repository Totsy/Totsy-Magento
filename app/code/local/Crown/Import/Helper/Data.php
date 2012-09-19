<?php
/**
 * 
 * @category 	Crown
 * @package 	Crown_Import 
 * @since 		1.0.0
 */
class Crown_Import_Helper_Data extends Mage_Core_Helper_Abstract {
    
    /**
     * Gets the import model based off the current session.
     * @since 1.0.0
     * @return Crown_Import_Model_Importhistory
     */
    public function getImportModel() {
		return Mage::getSingleton('crownimport/importhistory');
    }
    
    /**
     * Clears out the singleton used for the import model.
     * @since 1.0.0
     * @return Crown_Import_Helper_Data
     */
    public function resetImportModel() {
    	Mage::unregister('_singleton/crownimport/importhistory');
    	Mage::getSingleton ( 'adminhtml/session' )->setHpImportFormData ( null );
    	return $this;
    }
    
    /**
     * Stores the model in the session for loading later
     * @since 1.0.0
     * @param Crown_Import_Model_Importhistory $model
     * @return Crown_Import_Model_Importhistory
     */
    public function setImportModel( $model = null) {
    	$this->resetImportModel();
    	$importModel = Mage::getSingleton('crownimport/importhistory');
    	if (is_numeric($model)) {
    		$importModel->load($model);
    	} elseif($model instanceof Crown_Import_Model_Importhistory) {
    		$importModel->load($model->getId());
    	} else {
    		$importModel->setCreatedAt ( now () );
    		$importModel->save();
    	}
    	return $importModel;
    }
    
	/**
     * Gets the invalid characters for a uRapidFlow import
     * @since 1.0.0
     * @return array
     */
    public function getInvalidCharacters() {
    	if ( !isset($this->_invalid_characters) ) {
    		$info = Mage::getStoreConfig ( 'crownimport/urapidflow/invalid_characters' );
    		$info = preg_replace('/\s+/', ' ', $info);
    		$this->_invalid_characters = explode(' ', $info);
    	}
    	return $this->_invalid_characters;
    }
    
    /**
     * Gets the name of the default product store.
     * @since 1.0.0
     * @return string
     */
    public function getDefaultStore() {
    	if ( !isset($this->_store) ) {
    		$info = abs ( ( int ) Mage::getStoreConfig ( 'crownimport/general/store' ) );
    		$store = Mage::getModel('core/store')->load($info);
    		$this->_store = $store->getName();
    	}
    	return $this->_store;
    }
    
    /**
     * Gest the default website name for products.
     * @since 1.0.0
     * @return string
     */
    public function getDefaultWebsite() {
    	if ( !isset($this->_website) ) {
    		$info = abs ( ( int ) Mage::getStoreConfig ( 'crownimport/general/website' ) );
    		$website = Mage::getModel('core/website')->load($info);
    		$this->_website = $website->getCode();
    	}
    	return $this->_website;
    }
    
    /**
     * Gets the default attribute set name to be used for import.
     * @since 1.0.0
     * @return string
     */
    public function getDefaultAttributeSet() {
    	if (!isset($this->_attribute_set)) {
			$info = abs ( ( int ) Mage::getStoreConfig ( 'crownimport/general/attribute_set' ) );
			$set = Mage::getModel ( 'eav/entity_attribute_set' )->load ( $info );
			$this->_attribute_set = $set->getAttributeSetName();
    	}
		return $this->_attribute_set;
    }
    
    /**
     * Gets the default product status text to be used
     * @since 1.0.0
     * @return string
     */
    public function getDefaultStatus() {
    	$info = abs ( ( int ) Mage::getStoreConfig ( 'crownimport/general/status' ) );
    	return $info ? 'Enabled': 'Disabled';
    }
    
    /**
     * Gets the default tax class name to be used for new products
     * @since 1.0.0
     * @return string
     */
    public function getDefaultTaxClass() {
    	if (!isset($this->_tax_class)) {
			$info = abs ( ( int ) Mage::getStoreConfig ( 'crownimport/general/tax_class' ) );
			$set = Mage::getModel ( 'tax/class' )->load ( $info );
			$this->_tax_class = $set->getClassName();
    	}
		return $this->_tax_class;
    }
    
    /**
     * Gets the default if product is in stock
     * @since 1.0.0
     * @return string
     */
    public function getDefaultIsInStock() {
    	$info = abs ( ( int ) Mage::getStoreConfig ( 'crownimport/general/is_in_stock' ) );
    	return $info ? 'Yes':'No';
    }
    
    /**
     * Gets the default product weight to be used.
     * @since 1.0.0
     * @return float
     */
    public function getDefaultWeight() {
    	$info = Mage::getStoreConfig ( 'crownimport/general/weight' );
    	return $info;
    }
    
    /**
     * Gets the default product description text to be used.
     * @since 1.0.0
     * @return string
     */
    public function getDefaultDescription() {
    	$info = Mage::getStoreConfig ( 'crownimport/general/description' );
    	return $info;
    }
    
    /**
     * Gets the default product short description text to be used.
     * @since 1.0.0
     * @return string
     */
    public function getDefaultShortDescription() {
    	$info = Mage::getStoreConfig ( 'crownimport/general/short_description' );
    	return $info;
    }
}