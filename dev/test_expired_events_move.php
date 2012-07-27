<?php

require_once __DIR__ . '/../app/Mage.php';
Mage::app();

$obs = Mage::getModel('categoryevent/sortentry');

try {
	
	$obs->cleanUpExpiredEvents(null);
        
} catch(Exception $e) {
    echo "ERROR: ", $e->getMessage();
}

echo "Complete", PHP_EOL;
