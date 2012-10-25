<?php
/**
 *
 * @method Crown_Import_Model_Productimport setDefaultProductWebsiteCode(string $value)
 * @method string getDefaultProductWebsiteCode()
 * @method Crown_Import_Model_Productimport setDefaultProductAttributeSet(string $value)
 * @method string getDefaultProductAttributeSet()
 * @method Crown_Import_Model_Productimport setDefaultProductStatus(string $value)
 * @method string getDefaultProductStatus()
 * @method Crown_Import_Model_Productimport setDefaultProductTaxClass(string $value)
 * @method string getDefaultProductTaxClass()
 * @method Crown_Import_Model_Productimport setDefaultProductShortDescription(string $value)
 * @method string getDefaultProductShortDescription()
 * @method Crown_Import_Model_Productimport setDefaultProductDescription(string $value)
 * @method string getDefaultProductDescription()
 * @method Crown_Import_Model_Productimport setDefaultProductWeight(string $value)
 * @method string getDefaultProductWeight()
 * @method Crown_Import_Model_Productimport setDefaultProductIsInStock(string $value)
 * @method string getDefaultProductIsInStock()
 * @method Crown_Import_Model_Productimport setDefaultProductCategoryId(int $value)
 * @method int getDefaultProductCategoryId()
 * @method Crown_Import_Model_Productimport setDefaultProductVendorId(int $value)
 * @method int getDefaultProductVendorId()
 * @method Crown_Import_Model_Productimport setDefaultProductVendorCode(string $value)
 * @method string getDefaultProductVendorCode()
 * @method Crown_Import_Model_Productimport setDefaultProductPoId(int $value)
 * @method int getDefaultProductPoId()
 * @method Crown_Import_Model_Productimport setImportProfileModel(Unirgy_RapidFlow_Model_Profile $value)
 * @method Unirgy_RapidFlow_Model_Profile getImportProfileModel()
 *
 * @category 	Crown
 * @package 	Crown_Import
 * @since 		1.0.0
 */
class Crown_Import_Model_Productimport extends Crown_Import_Model_Import_Abstract {

	/**
	 * Attributes to be used for creating a configurable product.
	 * @since 1.0.0
	 * @var array
	 */
	protected  $_configurableAttributes = array('color','size');

	/**
	 * Max length for a sku. Restricted by DotCom.
	 * @since 1.0.0
	 * @var int
	 */
	const PRODUCT_SKU_MAX_LENGTH = 17;

	/**
	 * Load the core filters
	 * @since 1.0.0
	 * @return Crown_Import_Model_Productimport_Abstract
	 */
	protected function loadFilters() {
		$this->addRowFilter ( array (&$this, 'filterCategoryId'), 1 );
		$this->addRowFilter ( array (&$this, 'filterVendorCode'), 1 );
		$this->addRowFilter ( array (&$this, 'filterSku'), 2 );
		$this->addRowFilter ( array (&$this, 'filterWebsites'), 2 );
		$this->addRowFilter ( array (&$this, 'filterAttributeSet'), 2 );
		$this->addRowFilter ( array (&$this, 'filterStatus'), 2 );
		$this->addRowFilter ( array (&$this, 'filterShortDescription'), 2 );
		$this->addRowFilter ( array (&$this, 'filterDescription'), 2 );
		$this->addRowFilter ( array (&$this, 'filterWeight'), 2 );
		$this->addRowFilter ( array (&$this, 'filterTaxClassId'), 2 );
		$this->addRowFilter ( array (&$this, 'filterIsInStock'), 2 );
		$this->addRowFilter ( array (&$this, 'filterProductVisibility'), 2 );
		$this->addRowFilter ( array (&$this, 'filterProductInventoryStatus'), 2 );
		$this->addRowFilter ( array (&$this, 'filterMediaGallery'), 10 );

		$this->addAfterParseEvent( array (&$this, 'filterFindConfigurables') );
        $this->addAfterParseEvent( array (&$this, 'filterValidateMediaGallery') );

		$this->addAttributeFilter( 'image', array (&$this, 'filterRemoveBeginningSlash') );
		$this->addAttributeFilter( 'small_image', array (&$this, 'filterRemoveBeginningSlash') );
		$this->addAttributeFilter( 'thumbnail', array (&$this, 'filterRemoveBeginningSlash') );

		return parent::loadfilters();
	}

	/**
	 * (non-PHPdoc)
	 * @see Crown_Import_Model_Productimport_Abstract::run()
	 */
	public function run() {
		$this->loadDefaults();
		parent::run();
	}

	/**
	 * Set default values based off of user settings
	 * @since 1.0.0
	 * @return void
	 */
	protected function loadDefaults() {
		/* @var $helper Crown_Import_Helper_Data */
		$helper = Mage::helper('crownimport');
		$this->setDefaultProductAttributeSet($helper->getDefaultAttributeSet());
		$this->setDefaultProductDescription($helper->getDefaultDescription());
		$this->setDefaultProductIsInStock($helper->getDefaultIsInStock());
		$this->setDefaultProductShortDescription($helper->getDefaultShortDescription());
		$this->setDefaultProductStatus($helper->getDefaultStatus());
		$this->setDefaultProductTaxClass($helper->getDefaultTaxClass());
		$this->setDefaultProductWebsiteCode($helper->getDefaultWebsite());
		$this->setDefaultProductWeight($helper->getDefaultWeight());

		$this->addColumnNameMap('type','product.type');
		$this->addColumnNameMap('attribute_set','product.attribute_set');
		$this->addColumnNameMap('qty','stock.qty');
		$this->addColumnNameMap('category_id','category.ids');
		$this->addColumnNameMap('websites','product.websites');
	}

	/**
	 * Removes the begging slash off of paths
	 * @param string $value
	 * @since 1.0.4
	 * @return string
	 */
	public function filterRemoveBeginningSlash($value) {
		$value = ltrim($value, '/');
		return $value;
	}

	/**
	 * Finds configurable products
	 * @since 1.0.0
	 * @return Crown_Import_Model_Productimport
	 */
	public function filterFindConfigurables() {
		$baseSkus = array();
		foreach ($this->_productData as $sku => $data ) {
			if ( isset($data['product.type']) && 'configurable' == $data['product.type'] && isset($data['vendor_style']) && !empty($data['vendor_style'])) {
				$baseSkus[$data['vendor_style']] = $sku;
				$this->_superAttributesPerSku[$sku] = $this->_configurableAttributes;
			}
		}
		foreach ($this->_productData as $sku => $data ) {
			if ( isset($data['product.type']) && 'simple' == $data['product.type'] && isset($data['vendor_style']) && !empty($data['vendor_style'])) {
				if (isset( $baseSkus[$data['vendor_style']] )) {
					$this->_baseSkus[ $baseSkus[$data['vendor_style']] ][] = $sku;
					$this->_productData[$sku]['visibility'] = 'Not Visible Individually';
				}
			}
		}
		return $this;
	}

	/**
	 * Sets the inventory for a configurable product to follow globals and simples to follow the default setting.
	 * @param $_id tempData id
	 * @param $data Row data
	 * @since 1.0.0
	 * @return array
	 */
	public function filterProductInventoryStatus($_id, $data) {
		if ('configurable' == $data['product.type'] ) {
			$data['stock.use_config_manage_stock'] = 'yes';
			$data['stock.use_config_enable_qty_increments'] = 'yes';
			$data['stock.is_in_stock'] = 'yes';
			$this->_fields[] = 'stock.use_config_manage_stock';
			$this->_fields[] = 'stock.use_config_enable_qty_increments';
			$this->_fields[] = 'stock.is_in_stock';
		} elseif ('simple' == $data['product.type'] && $data['stock.qty'] > 0) {
			$data['stock.is_in_stock'] =  $this->getDefaultProductIsInStock() ? 'yes': 'no';
			$this->_fields[] = 'stock.is_in_stock';
		}

		return $data;
	}

	/**
	 * Sets the default website if it's not set
	 * @param mixed int|string $_id
	 * @param array $data
	 * @since 1.0.0
	 * @return array
	 */
	public function filterWebsites($_id, $data) {
		if (!isset($data['product.websites'])) {
			$data['product.websites'] = $this->getDefaultProductWebsiteCode();
			$this->_fields[] = 'product.websites';
		}
		return $data;
	}

	/**
	 * Sets the default attribut set if it's not set
	 * @param mixed int|string $_id
	 * @param array $data
	 * @since 1.0.0
	 * @return array
	 */
	public function filterAttributeSet($_id, $data) {
		if (!isset($data['product.attribute_set']) || empty($data['product.attribute_set'])) {
			$data['product.attribute_set'] = $this->getDefaultProductAttributeSet();
			$this->_fields[] = 'product.attribute_set';
		}
		return $data;
	}

	/**
	 * Sets the default product status if it's not set
	 * @param mixed int|string $_id
	 * @param array $data
	 * @since 1.0.0
	 * @return array
	 */
	public function filterStatus($_id, $data) {
		if (!isset($data['status'])) {
			$data['status'] = $this->getDefaultProductStatus();
			$this->_fields[] = 'status';
		}
		return $data;
	}

	/**
	 * Generates a sku for the product if it's not set
	 * @param mixed int|string $_id
	 * @param array $data
	 * @since 1.0.0
	 * @return array
	 */
	public function filterSku($_id, $data) {
		if ( isset($data['product.type']) && !isset($data['sku']) ) {
            $base = isset($data['vendor_id']) ? $data['vendor_id']: $this->getDefaultProductVendorId();
			$data['sku'] = $this->_generateProductSku ( $base );
		}
		return $data;
	}

	/**
	 * Generates a sku for a product
	 * @param mixed $base
	 * @since 1.0.0
	 * @return string
	 */
	protected function _generateProductSku($base) {
		$sku = $base . '-' . base_convert ( time (), 10, 36 ) . base_convert ( rand ( 0, base_convert ( 'zzz', 36, 10 ) ), 10, 36 );
		return substr ( $sku, 0, self::PRODUCT_SKU_MAX_LENGTH );
	}

	/**
	 * Filter to make sure the 'short description' exist in the import data with default values.
	 * @param mixed int|string $_id
	 * @param array $data
	 * @since 1.0.0
	 * @return array
	 */
	public function filterShortDescription($_id, $data) {
		if (!isset($data['short_description'])) {
			$data['short_description'] = $this->getDefaultProductShortDescription();
			$this->_fields[] = 'short_description';
		}
		return $data;
	}

	/**
	 * Filter to make sure the 'description' exist in the import data with default values.
	 * @param mixed int|string $_id
	 * @param array $data
	 * @since 1.0.0
	 * @return array
	 */
	public function filterDescription($_id, $data) {
		if (!isset($data['description'])) {
			$data['description'] = $this->getDefaultProductDescription();
			$this->_fields[] = 'description';
		}
		return $data;
	}

	/**
	 * Filter to make sure the 'weight' exist in the import data with default values.
	 * @param mixed int|string $_id
	 * @param array $data
	 * @since 1.0.0
	 * @return array
	 */
	public function filterWeight($_id, $data) {
		if ( 'simple' == $data['product.type'] && ( !isset($data['weight']) || ( isset($data['weight']) && empty($data['weight']) ) ) ) {
			$data['weight'] = $this->getDefaultProductWeight();
			$this->_fields[] = 'weight';
		}
		return $data;
	}

	/**
	 * Filter to make sure the 'tax class id' exist in the import data with default values.
	 * @param mixed int|string $_id
	 * @param array $data
	 * @since 1.0.0
	 * @return array
	 */
	public function filterTaxClassId($_id, $data) {
		if (!isset($data['tax_class_id'])) {
			$data['tax_class_id'] = $this->getDefaultProductTaxClass();
			$this->_fields[] = 'tax_class_id';
		}
		return $data;
	}

	/**
	 * Filter to make sure the 'is in stock' exist in the import data with default values.
	 * @param mixed int|string $_id
	 * @param array $data
	 * @since 1.0.0
	 * @return array
	 */
	public function filterIsInStock($_id, $data) {
		if (!isset($data['is_in_stock'])) {
			$data['is_in_stock'] = $this->getDefaultProductIsInStock();
			$this->_fields[] = 'is_in_stock';
		}
		return $data;
	}

	/**
	 * Filter to set the default category ID
	 * @param mixed int|string $_id
	 * @param array $data
	 * @since 1.0.0
	 * @return array
	 */
	public function filterCategoryId($_id, $data) {
		if (!isset($data['category.ids'])) {
			$data['category.ids'] = $this->getDefaultProductCategoryId();
			$this->_fields[] = 'category.ids';
		}
		return $data;
	}

	/**
	 * Filter to set the default vendor code
	 * @param mixed int|string $_id
	 * @param array $data
	 * @since 1.0.0
	 * @return array
	 */
	public function filterVendorCode($_id, $data) {
		if (!isset($data['vendor_code'])) {
			$data['vendor_code'] = $this->getDefaultProductVendorCode();
			$this->_fields[] = 'vendor_code';
		}
		return $data;
	}

	/**
	 * Add media gallery images
	 * @param mixed int|string $_id
	 * @param array $data
	 * @since 1.0.4
	 * @return array
	 */
	public function filterMediaGallery($_id, $data) {
		if (isset($data['media_gallery']) && isset($data['sku'])) {
			$this->_media_gallery[$data['sku']] = explode(',', $data['media_gallery']);
			unset($data['media_gallery']);
			if (isset($this->_fields['media_gallery'])) {
				unset($this->_fields['media_gallery']);
			}
		}
		return $data;
	}

	/**
	 * Filter to set product visibility.
	 * @param mixed int|string $_id
	 * @param array $data
	 * @since 1.0.0
	 * @return array
	 */
	public function filterProductVisibility($_id, $data) {
		if (!isset($data ['visibility']) || empty($data ['visibility'])) {
			// All products are visible by default
			$data ['visibility'] = 'Catalog, Search';
			$this->_fields[] = 'visibility';
		}
		return $data;
	}

    /**
     * Filter to validate media image files
     * @since 1.3.0
     * @return void
     */
    public function filterValidateMediaGallery() {
        $errorMessages = array();
        $profile = $this->getImportProfileModel();
        if(!empty($this->_media_gallery)) {
            /* @var $mediaHlper Crown_Import_Helper_Data */
            $mediaHlper = Mage::helper('crownimport');

            foreach($this->_media_gallery as $sku => $mediaImages) {
                foreach ($mediaImages as $mediaImage) {
                    // Check for media image on server or remote host
                    try {
                        $mediaHlper->checkForValidImageFiles( $mediaImage, $profile );
                    } catch (Exception $e ) {
                        $errorMessages[$sku][] = $e->getMessage();
                    }
                }
            }
        }
        $profile->setData( 'error_messages', $errorMessages)->save();
    }
}

