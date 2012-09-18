<?php
/**
 * 
 * @category 	Crown
 * @package 	Crown_Import 
 * @since 		1.0.0
 */
class Crown_Import_Model_Mysql4_Importhistory extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('crownimport/importhistory', 'import_id');
    }
}