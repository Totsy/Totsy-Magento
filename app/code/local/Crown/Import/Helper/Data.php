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

    /**
     * Checks to see if a valid image file exist.
     * @param $filename
     * @param Unirgy_RapidFlow_Model_Profile $profile
     * @since 1.1.0
     * @return boolean
     */
    public function checkForValidImageFiles( $filename, $profile ) {
        $remote = preg_match('#^https?:#', $filename);
        if ($remote) {
            return $this->checkImageFileRemote($filename);
        } else {
            return $this->checkImageFileLocal($filename, $profile);
        }
    }

    /**
     * Checks to see if a local file image exist for import.
     * @param $filename
     * @param Unirgy_RapidFlow_Model_Profile $profile
     * @throws Exception
     * @since 1.1.0
     * @return bool
     */
    public function checkImageFileLocal( $filename, Unirgy_RapidFlow_Model_Profile $profile ) {
        $imagesFromDir = $profile->getImagesBaseDir();

        $fromFilename = $imagesFromDir . DS . ltrim($filename, DS);
        $fromExists = is_readable($fromFilename);

        if (!$fromExists) {
            throw new Exception('Image file not found.');
            return false;
        }
        return true;
    }

    /**
     * Checks to see if a remote file exist.
     * @param $filename
     * @throws Exception
     * @since 1.1.0
     * @return bool
     */
    public function checkImageFileRemote( $filename ) {
        if (!$this->_downloadRemoteImages) {
            throw new Exception('Remote image file download is not allowed.');
            return false;
        }

        $ch = curl_init($filename);

        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = $retcode == '200';

        if ( !$result ) {
            throw new Exception('Remote image file not found.');
            return false;
        }
        return true;
    }

}
