<?php
/**
 * 
 * @category 	Crown
 * @package 	Crown_Import 
 * @since 		1.0.0
 */
class Crown_Import_Model_Importhistory extends Mage_Core_Model_Abstract {
	
	/**
	 * Import in progress or abandoned
	 * @since 1.0.0
	 * @var int
	 */
	const IMPORT_STATUS_NEW = 0;
	
	/**
	 * Import completed
	 * @since 1.0.0
	 * @var int
	 */
	const IMPORT_STATUS_COMPLETE = 1;
	
	/**
	 * Import process is currently running
	 * @since 1.0.0
	 * @var int
	 */
	const IMPORT_STATUS_RUNNING = 2;
	
	/**
	 * Makes sure the status is set correctly based off of current operation
	 * @since 1.0.0
	 * @return Crown_Import_Model_Importhistory
	 */
	public function statusCheck() {
		$uRapidflowRunningStatus = array('running','pending','paused');
		switch ( $this->getStep() ) {
			case 'validation':
			case 'product-import':
				if ( $this->getUrapidflowProfile() ) {
					$profile = $this->getUrapidflowProfile();
					if ( in_array($profile->getRunStatus(), $uRapidflowRunningStatus) ) {
						$this->setStatus(self::IMPORT_STATUS_RUNNING);
						break;
					}
				}
				$this->setStatus(self::IMPORT_STATUS_NEW);
				break;
			case 'product-extra-import':
				if ( $this->getUrapidflowProfileIdProductExtra() ) {
					$profileExtra = $this->getUrapidflowProfileProductExtra();
					if ( in_array($profileExtra->getRunStatus(), $uRapidflowRunningStatus) ) {
						$this->setStatus(self::IMPORT_STATUS_RUNNING);
						break;
					}
				}
				$this->setStatus(self::IMPORT_STATUS_NEW);	
				break;
			case 'complete':
				$this->setStatus(self::IMPORT_STATUS_COMPLETE);
				break;
			case 'import':
			default:
				$this->setStatus(self::IMPORT_STATUS_NEW);
		}
		$this->save();
		return $this;
	}
	
	/**
	 * Gets the labels for import status
	 * @since 1.0.0
	 * @return array
	 */
    public function getGridStatusArray(){
        return array(
			self::IMPORT_STATUS_COMPLETE	=> 'Complete', 
			self::IMPORT_STATUS_NEW			=> 'Not Finished',
			self::IMPORT_STATUS_RUNNING		=> 'Running',
        );
    }
	
	/**
	 * (non-PHPdoc)
	 * @see Varien_Object::_construct()
	 */
	public function _construct() {
		parent::_construct ();
		$this->_init ( 'crownimport/importhistory' );
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Mage_Core_Model_Abstract::_beforeSave()
	 */
	protected function _beforeSave() {
    	if (null == $this->getData('created_at')) {
			$this->setData('created_at', now () );
		}
		$this->setData('updated_at', now () );
		return parent::_beforeSave();
	}
	
    /**
     * Clones a urapidflow profile
     * @param int $uRapidFlowProfileId
     * @throws Exception
     * @return Unirgy_RapidFlow_Model_Profile
     */
	protected function _cloneUrapidflowProfile($uRapidFlowProfileId) {
		$parentProfile = Mage::getModel ( 'urapidflow/profile' )->load ( $uRapidFlowProfileId );
		if ($parentProfile->getId ()) {
			$profile = clone $parentProfile;
			$profile->unsetData ( 'id' );
			$profile->unsetData ( 'profile_id' );
			$_title = ! $this->getImportTitle () ? ' Copy ' . $this->getId(): ': ' . $this->getImportTitle ();
			$profile->setTitle ( $profile->getTitle () . $_title );
			$profile->save ();
		} else {
			throw new Exception ( 'No valid uRapidFlowProfile Loaded' );
		}
		return $profile;
	}
	
	/**
     * Gets the uRapidFlow profile object for this import.
     * @since 1.0.0
     * @return Unirgy_RapidFlow_Model_Profile
     */
    public function getUrapidflowProfile() {
    	if ( !$this->hasData('urapidflow_profile')) {
    		$profile = $this->_getUrapidflowProfile();
    	}
    	return $this->getData('urapidflow_profile');
    }
    
	/**
     * Loads or creates a uRapidflow Profile
     * @throws Exception
     * @since 1.0.0
     * @return Unirgy_RapidFlow_Model_Profile
     */
    protected function _getUrapidflowProfile() {
    	if ( !$this->hasData('urapidflow_profile') && !$this->getData('urapidflow_profile_id') && self::IMPORT_STATUS_COMPLETE != $this->getStatus()) {
	    	$uRapidFlowProfileId = Mage::getStoreConfig ( 'crownimport/urapidflow/profile' );
			$profile = $this->_cloneUrapidflowProfile($uRapidFlowProfileId);
			$this->setData('urapidflow_profile', $profile);
	    	$this->setData('urapidflow_profile_id', $profile->getId());
	    	$this->save();
    	} elseif ( $this->getData('urapidflow_profile_id') ) {
    		$profile = Mage::getModel ( 'urapidflow/profile' )->load ($this->getData('urapidflow_profile_id'));
    		if ( $profile->getId() ) {
    			$this->setData('urapidflow_profile', $profile);
	    		$this->setData('urapidflow_profile_id', $profile->getId());
	    		$this->save();
    		} else {
    			$this->unsetData('urapidflow_profile');
	    		$this->unsetData('urapidflow_profile_id');
	    		$this->save();
	    		throw new Exception('Unable to load uRapidFlow Profile. It may have been deleted.');
    		}
    	} else {
    		throw new Exception('Unable to create uRapidFlow Profile');
    	}
    	return $profile;
    }
	
    /**
     * Creates a clone of the default profile.
     * @since 1.0.0
     * @return int
     */
    public function getUrapidflowProfileId() {
    	if ( !$this->hasData('urapidflow_profile_id') ) {
			$profile = $this->getUrapidflowProfile();
			if ($profile->getId ()) {
				$this->setData('urapidflow_profile_id', $profile->getId());
			}
    	}
    	return $this->getData('urapidflow_profile_id');
    }
    
	/**
     * Creates a clone of the default profile for product extra.
     * @since 1.0.0
     * @return Unirgy_RapidFlow_Model_Profile
     */
    public function getUrapidflowProfileProductExtra() {
		if ( !$this->hasData('urapidflow_profile_product_extra')) {
    		$profile = $this->_getUrapidflowProfileProductExtra();
    	}
    	return $this->getData('urapidflow_profile_product_extra');
    }
    
	/**
     * Loads or creates a uRapidflow Profile
     * @throws Exception
     * @since 1.0.0
     * @return Unirgy_RapidFlow_Model_Profile
     */
    protected function _getUrapidflowProfileProductExtra() {
    	if ( !$this->hasData('urapidflow_profile_product_extra') && !$this->getData('urapidflow_profile_id_product_extra') && self::IMPORT_STATUS_COMPLETE != $this->getStatus()) {
	    	$uRapidFlowProfileId = Mage::getStoreConfig ( 'crownimport/urapidflow/profile_product_extra' );
			$profile = $this->_cloneUrapidflowProfile($uRapidFlowProfileId);
			$this->setData('urapidflow_profile_product_extra', $profile);
	    	$this->setData('urapidflow_profile_id_product_extra', $profile->getId());
	    	$this->save();
    	} elseif ( $this->getData('urapidflow_profile_id_product_extra') ) {
    		$profile = Mage::getModel ( 'urapidflow/profile' )->load ($this->getData('urapidflow_profile_id_product_extra'));
    		if ( $profile->getId() ) {
    			$this->setData('urapidflow_profile_product_extra', $profile);
	    		$this->setData('urapidflow_profile_id_product_extra', $profile->getId());
	    		$this->save();
    		} else {
    			$this->unsetData('urapidflow_profile_product_extra');
	    		$this->unsetData('urapidflow_profile_id_product_extra');
	    		$this->save();
	    		throw new Exception('Unable to load uRapidFlow Profile. It may have been deleted.');
    		}
    	} else {
    		throw new Exception('Unable to create uRapidFlow Profile');
    	}
    	return $profile;
    }
    
	/**
     * Creates a clone of the default profile for product extra.
     * @since 1.0.0
     * @return int
     */
    public function getUrapidflowProfileProductExtraId() {
		if ( !$this->hasData('urapidflow_profile_id_product_extra') ) {
			$profile = $this->getUrapidflowProfileProductExtra();
			if ($profile->getId ()) {
				$this->setData('urapidflow_profile_id_product_extra', $profile->getId());
			}
    	}
    	return $this->getData('urapidflow_profile_id_product_extra');
    }

}