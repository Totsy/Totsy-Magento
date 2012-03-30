<?php

class Harapartners_Import_Model_Mysql4_Import extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the import_id refers to the key field in your database table.
        $this->_init('import/import', 'import_import_id');
    }
}