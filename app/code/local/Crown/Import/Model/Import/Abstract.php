<?php
/**
 * 
 * @method Crown_Import_Model_Import_Abstract setSourceFile(string $value)
 * @method string getSourceFile()
 * @method string getFilename()
 * @method string getProductExtraFilename()
 * @method Crown_Import_Model_Import_Abstract setFileBaseDir(string $value)
 * @method string getFileBaseDir()
 * @method boolean getHasConfigurableProducts()
 * 
 * @category 	Crown
 * @package 	Crown_Import 
 * @since 		1.0.0
 */
class Crown_Import_Model_Import_Abstract extends Mage_Core_Model_Abstract {
	
	/**
	 * Number of columns that exist
	 * @since 1.0.0
	 * @var int
	 */
	protected $_offset;
	
	/**
	 * Stores the data to be written
	 * @since 1.0.0
	 * @var array
	 */
	protected $_productData = array();
	
	/**
	 * Stores data before it's commited to be written
	 * @since 1.0.0
	 * @var array
	 */
	protected $_tempData = array();
	
	/**
	 * Stores the static columns.
	 * @since 1.0.0
	 * @var array
	 */
	protected $_staticColumns = array();
	
	/**
	 * Stores new fields to be added to the export.
	 * @since 1.0.0
	 * @var array
	 */
	protected $_fields = array();
	
	/**
	 * Stores the skus to be imported
	 * @since 1.0.0
	 * @var array
	 */
	protected $_skus = array();
	
	/**
	 * Current data record
	 * @since 1.0.0
	 * @var array
	 */
	protected $_currentRecord = array();
	
	/**
	 * Filters attributes with methods.
	 * @since 1.0.0
	 * @var array
	 */
	protected $_attribute_filters = array();
	
	/**
	 * Filters a row of data with methods.
	 * @since 1.0.0
	 * @var array
	 */
	protected $_row_filters = array();
	
	/**
	 * Methods to run after the data import and parse process
	 * @since 1.0.0
	 * @var array
	 */
	protected $_after_fiter_events = array();
	
	/**
	 * Column name mapping.
	 * @since 1.0.0
	 * @var array
	 */
	protected $_columnNameConvert = array();
	
	/**
	 * Holds the configurable product sku and it's children skus
	 * @since 1.0.0
	 * @var array
	 */
	protected $_baseSkus = array();
	
	/**
	 * Holds the super attributes to be used with each configurable product.
	 * @since 1.0.0
	 * @var array
	 */
	protected $_superAttributesPerSku = array();
	
	/**
	 * Sets the filename to be used. Will be suffixed with date and filename automatically.
	 * @param string $fname
	 * @since 1.0.0
	 * @return Crown_Import_Model_Import_Abstract
	 */
	public function setFilename( $fname ) {
		$_filename = $this->filenameFilter($fname);
		$this->setData('filename', $_filename . '_' . date('Y') . '_' . date('m') . '_' . date('d') . '.csv');
		return $this;
	}
	
	/**
	 * Sets the filename to be used for product extra import. Will be suffixed with date and filename automatically.
	 * @param string $fname
	 * @since 1.0.0
	 * @return Crown_Import_Model_Import_Abstract
	 */
	public function setProductExtraFilename($fname) {
		$_filename = $this->filenameFilter($fname);
		$this->setData('product_extra_filename', $_filename . '_' . date('Y') . '_' . date('m') . '_' . date('d') . '.csv');
		return $this;
	}
	
	/**
	 * Makes sure a filename is filesystem safe
	 * @since 1.0.0
	 * @param string $fname
	 * @return string
	 */
	protected function filenameFilter($fname) {
		$pattern = "/([[:alnum:]_\.-]*)/";
		$_filename = str_replace(str_split(preg_replace($pattern,'_',$fname)),'_',$fname);
		return $_filename;
	}
	
	/**
	 * Filters a single attribute of data upon loading from the source.
	 * @param string $attribute
	 * @param array $method
	 * @since 1.0.0
	 * @return Crown_Import_Model_Import_Abstract
	 */
	public function addAttributeFilter($attribute, array $method) {
		$this->_attribute_filters[$attribute][] = $method;
		return $this;
	}
	
	/**
	 * Adds a filter for a whole row of data
	 * @param array $method
	 * @param int $priority
	 * @since 1.0.0
	 * @return Crown_Import_Model_Import_Abstract
	 */
	public function addRowFilter(array $method, $priority = 10) {
		$priority = intval($priority);
		$this->_row_filters[$priority][] = $method;
		return $this;
	}
	
	/**
	 * Adds an event to run after the import and parse
	 * @param array $method
	 * @param int $priority
	 * @since 1.0.0
	 * @return Crown_Import_Model_Import_Abstract
	 */
	public function addAfterParseEvent(array $method, $priority = 10) {
		$priority = intval($priority);
		$this->_after_fiter_events[$priority][] = $method;
		return $this;
	}
	
	/**
	 * Run export and filters
	 * @since 1.0.0
	 */
	public function run() {
		// Execute methods in order of operations
		$this->loadFilters()
			->parseSourceFile()
			->runAfterImportParseEvents()
			->createProductExtraImportFile()
			->createImportFile();
	}
	
	/**
	 * Run the filters on an attribute
	 * @param string $attribute
	 * @param mixed int|string $value
	 * @since 1.0.0
	 * @return Crown_Import_Model_Import_Abstract
	 */
	protected function runAttributeFilter( $attribute, $value ) {
		foreach ( $this->_attribute_filters[$attribute] as $method ) {
			$value = call_user_func($method, $value);
		}
		return $value;
	}
	
	/**
	 * Run filters on a row of data.
	 * @param string $_id
	 * @param array $data
	 * @since 1.0.0
	 * @return Crown_Import_Model_Import_Abstract
	 */
	protected function runRowFilter($_id, $data) {
		reset($this->_row_filters);
		do {
			foreach( (array) current($this->_row_filters) as $method ){
				if (is_array($method) || is_string($method)) {
					$data = call_user_func($method, $_id, $data);
				}
			}
		} while ( next($this->_row_filters) !== false );
		return $this;
	}
	
	/**
	 * Run filters on a row of data.
	 * @param string $_id
	 * @param array $data
	 * @since 1.0.0
	 * @return Crown_Import_Model_Import_Abstract
	 */
	protected function runAfterImportParseEvents() {
		reset($this->_after_fiter_events);
		do {
			foreach( (array) current($this->_after_fiter_events) as $method ){
				if (is_array($method) || is_string($method)) {
					call_user_func($method);
				}
			}
		} while ( next($this->_after_fiter_events) !== false );
		return $this;
	}
	
	/**
	 * Load the core filters
	 * @since 1.0.0
	 * @return Crown_Import_Model_Import_Abstract
	 */
	protected function loadFilters() {
		$this->addRowFilter ( array (&$this, 'filterSaveData' ), 100 );
		return $this;
	}
	
	/**
	 * Moves data from tempoary storage to commitment
	 * @param mixed int|string $_id
	 * @param array $data
	 * @since 1.0.0
	 * @return array
	 */
	public function filterSaveData($_id,$data) {
		if (isset($data['sku'])) {
			$sku = $data['sku'];
			unset($data['sku']);
			$this->_productData[$sku] = $data;
			$this->_skus[] = $sku;
			unset($this->_tempData[$_id]);
		}
		return $data;
	}
	
	/**
	 * Adds a mapping to change a column name.
	 * @param string $name Original Column name from import
	 * @param string $newName New column name
	 * @since 1.0.0
	 * @return Crown_Import_Model_Import_Abstract
	 */
	public function addColumnNameMap($name, $newName) {
		$this->_columnNameConvert[$name] = $newName;
		return $this;
	}
	
	/**
	 * Converts input column names into uRapidFlow ones.
	 * @param string $name
	 * @since 1.0.0
	 * @return string
	 */
	protected function getColumnName($name) {
		$name = strtolower($name);
		if (isset($this->_columnNameConvert[$name])) {
			return $this->_columnNameConvert[$name];
		}
		return $name;
	}
	
	/**
	 * Gets the data from the source and stores and validates for write.
	 * Also executes filters on static columns.
	 * @since 1.0.0
	 * @return Crown_Import_Model_Import_Abstract
	 */
	protected function parseSourceFile() {
		if (($handle = fopen ( $this->getSourceFile(), "r" )) !== false) {
			$headerRow = true;
			$hasSku = false;
			$line = 0;
			while ( ($data = fgetcsv ( $handle, 1000, "," )) !== false ) {
				$this->_offset = count ( $data );
				$line++;
				
				// Get header static field names
				if ( $headerRow ) {
					for ( $i = 0; $i < $this->_offset; $i++ )
						$this->_staticColumns[$i] = $this->getColumnName($data[$i]);
					$hasSku = isset($this->_staticColumns['sku']);
					$headerRow = false;
					continue;
				}
				
				// Filter static fields
				for ( $i = 0; $i < $this->_offset; $i++ ) {
					$this->_currentRecord [$this->_staticColumns[$i] ] = isset($data[$i]) ? 
					(isset($this->_attribute_filters[$this->_staticColumns[$i]]) ? 
						$this->runAttributeFilter($this->_staticColumns[$i], $data[$i]): $data[$i]):
					null;
				}
				
				$_id = $line;
				
				$this->_tempData[$_id] = $this->_currentRecord;
				$this->_currentRecord = null;
			}
			fclose ( $handle );
		}
		
		ksort($this->_row_filters);
		
		// Filter rows of data
		foreach ( $this->_tempData as $_id => $data ) {
			$this->runRowFilter($_id, $data);
		}
		
		return $this;
	}
	
	/**
	 * Creates the import csv file for magento
	 * @since 1.0.0
	 * @return Crown_Import_Model_Import_Abstract
	 */
	protected function createImportFile() {
		$this->_fields = array_unique($this->_fields);
		$this->_skus = array_unique($this->_skus);
		$this->_staticColumns = array_unique($this->_staticColumns);
		if (($fp = fopen ( $this->getFileBaseDir() . DS . $this->getFilename(), 'w' )) !== false) {
			// Load headers
			$_headers = array_unique(array_merge($this->_fields, $this->_staticColumns));
			asort($_headers);
			$_columns = $_headers;
			array_unshift($_headers, 'sku');
			fputcsv ( $fp, $_headers );
			// Load sku data
			foreach ( $this->_skus as $_SKU ) {
				reset($_columns);
				$data = array();
				foreach ( $_columns as $field ) {
					$data[] = isset($this->_productData[$_SKU][$field]) && strtolower($this->_productData[$_SKU][$field]) != 'null'
						? $this->_productData[$_SKU][$field]: null;
				}
				if ( !empty( $data ) ) {
					array_unshift($data, $_SKU);
					fputcsv ( $fp, $data );
				}
			}
			fclose ( $fp );
		}
		return $this;
	}
	
	/**
	 * Creates the product import file for extra data.
	 * @since 1.0.0
	 * @return Crown_Import_Model_Import_Abstract
	 */
	protected function createProductExtraImportFile() {
		if (!empty($this->_baseSkus)) {
			$this->setData('has_configurable_products', true);
			if (($fp = fopen ( $this->getFileBaseDir() . DS . $this->getProductExtraFilename(), 'w' )) !== false) {
				// Add super attributes to configurables
				$data = array(
					'##CPSA','sku','attribute_code','position','label'
				);
				fputcsv ( $fp, $data );
				foreach ( $this->_baseSkus as $_baseSku => $childSkusArray ) {
					if ( !isset($this->_superAttributesPerSku[$_baseSku]) ) continue;
					$pos = 0;
					foreach ($this->_superAttributesPerSku[$_baseSku] as $superAttribute) {
						$data = array(
							'CPSA',$_baseSku,$superAttribute,$pos,ucfirst($superAttribute)
						);
						fputcsv ( $fp, $data );
						$pos++;
					}
				}
				
				// Add linking between configurables and simple products
				$data = array(
					'##CPSI','sku','linked_sku'
				);
				fputcsv ( $fp, $data );
				foreach ( $this->_baseSkus as $_baseSku => $childSkusArray ) {
					foreach ( $childSkusArray as $childSku ) {
						$data = array(
							'CPSI',$_baseSku,$childSku
						);
						fputcsv ( $fp, $data );
					}
				}
				fclose ( $fp );
			}
		}
		return $this;
	}
}