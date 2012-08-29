<?php

require_once 'abstract.php';

class Totsy_Shell_Run_Upgrade extends Mage_Shell_Abstract
{

    public function run()
    {
        set_time_limit(0);
        ini_set('memory_limit', '2G');
        ini_set('apc.enabled', false);

        $app = Mage::app('admin');
        $app->cleanCache();

        ob_start();
        Mage_Core_Model_Resource_Setup::applyAllUpdates();
        Mage_Core_Model_Resource_Setup::applyAllDataUpdates();
        ob_end_clean();
    }
}

$shell = new Totsy_Shell_Run_Upgrade();
$shell->run();
