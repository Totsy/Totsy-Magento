<?php

class Harapartners_Import_Model_Mysql4_Importset extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the import_id refers to the key field in your database table.
        $this->_init('import/importset', 'import_importset_id');
    }
}