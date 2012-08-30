<?php
require_once 'abstract.php';

class Mage_Shell_Compiler extends Mage_Shell_Abstract
{
    /**
     * Run script
     *
     */
    public function run()
    {
		Mage::helper('CustomerIndex')->reindexCustomerFlat();
    }
}

$shell = new Mage_Shell_Compiler();
$shell->run();
